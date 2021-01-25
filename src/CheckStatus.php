<?php


namespace GreatCode;


use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class CheckStatus
{
    private $HOST;
    private $GREATCODE_STORAGE = "greatcode";
    private $domain;
    private $product_uuid;

    public function __construct($product_uuid, $domain)
    {
        $this->product_uuid = $product_uuid;
        $this->domain = $domain;

        $this->HOST = base64_decode(base64_decode('YUhSMGNEb3ZMMmR5WldGMFkyOWtaWFJsWVcwdVkyOXQ=')) . '/api';
    }

    public function is_cli()
    {
        return !http_response_code();
    }

    private function check_path($path)
    {
        return Storage::exists($this->GREATCODE_STORAGE . "/$path");
    }

    private function update_log($type = 'domain', $content = null)
    {
        switch ($type) {
            case "domain":
                Storage::disk('local')->put($this->GREATCODE_STORAGE . '/domain', $content);
                return true;
            case "user":
                Storage::disk('local')->put($this->GREATCODE_STORAGE . '/user', $content);
                return true;
            case "status":
                Storage::disk('local')->put($this->GREATCODE_STORAGE . '/status', $content);
                return true;

            default:
                return true;
        }
    }

    public function init()
    {
        if (!$this->is_cli()) {
            $server_domain = parse_url(URL::full())['host'];
            if ($this->check_path('domain')) {
                try {
                    $domain = parse_url(Storage::disk('local')->get($this->GREATCODE_STORAGE . '/domain'))['host'];
                    if ($domain != $server_domain) {
                        $this->check_domain($server_domain);
                    }
                } catch (FileNotFoundException $e) {
                    $this->check_domain($server_domain);
                }
            } else {
                $this->check_domain($server_domain);
            }
        }
    }

    private function check_domain($domain)
    {
        $response = Http::post($this->HOST . '/domain/check', [
            "domain" => "http://$domain",
            "product_uuid" => config('greatcode.product_UUID')
        ]);

        if ($response->status() == 200) {
            $array = $response->json()['data'];
            $this->update_log('domain', $array['domain']);
            $this->update_log('user', $array['user']);
            $this->update_log('status', 1);
        } else {
            abort(404);
        }
    }
}
