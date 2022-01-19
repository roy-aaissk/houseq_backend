<?php

namespace App\Http\Middleware;

use Auth0\SDK\Helpers\JWKFetcher;
use Auth0\SDK\Helpers\Tokens\AsymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\IdTokenVerifier;
use Auth0\SDK\Helpers\Tokens\SymmetricVerifier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Closure;

class CheckIdToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // return $next($request);

        if(empty($request->bearerToken())) {
            return response()->json(["message" => "Token dose not exist"], 401);
        }

        $id_token = $request->bearerToken();

        $id_token_header = explode('.', $id_token)[0];

        try {
            $token_alg = json_decode(base64_decode($id_token_header))->alg;
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 401);
        }

        $token_issuer = 'https://' . config('const.auth0.domain') . '/';

        $signature_verifier = null;

        if ('RS256' === $token_alg) {
            // 指定したissuerからjwksを取得し、証明書(CERTIFICATE)で取得する
            $jwks_fetcher = new JWKFetcher();
            $jwks = $jwks_fetcher->getKeys($token_issuer.'.well-known/jwks.json');
            $signature_verifier = new AsymmetricVerifier($jwks);
        } else if ('HS256' === $token_alg) {
            $signature_verifier = new SymmetricVerifier(config('const.auth0.client_secret'));
        } else {
            return response()->json(["message" => "Invalid alg"]);
        }

        $token_verifier = new IdTokenVerifier(
            $token_issuer,
            config('const.auth0.client_id'),
            $signature_verifier
        );

        // トークンを検証する
        try {
            $decoded_token = $token_verifier->verify($id_token);
        } catch (\Exception $e) {
            logger()->info('id_tokenの初回検証に失敗しました。 Caught: Exception - '.$e->getMessage());

            // 検証に失敗したら一度だけjwksを最新のものに更新し、再度検証する
            // 実際に実装する際はprivate functionなどに抜き出して共通化すると良いでしょう
            $jwks_fetcher = new JWKFetcher();
            $new_jwks = $jwks_fetcher->getKeys($token_issuer.'.well-known/jwks.json');
            Cache::put('auht0_jwks_key', $new_jwks, 43200);

            $new_signature_verifier = new AsymmetricVerifier($new_jwks);

            $new_token_verifier = new IdTokenVerifier(
                $token_issuer,
                config('const.auth0.client_id'),
                $new_signature_verifier
            );

            try {
                $decoded_token = $new_token_verifier->verify($id_token);
            } catch (\Exception $e) {
                logger()->warning('id_tokenの２回目の検証に失敗しました。');
                return response()->json(["message" => $e->getMessage()], 401);
            };
        }

        // user_idを$requestに追加する。
        $request->merge([
            'auth0_user_id' => $decoded_token['sub']
        ]);

        return $next($request);
    }
}
