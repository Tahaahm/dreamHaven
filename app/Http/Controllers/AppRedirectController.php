<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppRedirectController extends Controller
{
    const IOS_URL     = 'https://apps.apple.com/us/app/dream-mulk/id6756894199';
    const ANDROID_URL = 'https://play.google.com/store/apps/details?id=com.dreammulk.dreamhaven';
    const FALLBACK_URL = 'https://dreammulk.com'; // shown on desktop

    public function redirect(Request $request)
    {
        $ua = strtolower($request->userAgent() ?? '');

        $isIos     = str_contains($ua, 'iphone')
            || str_contains($ua, 'ipad')
            || str_contains($ua, 'ipod');

        $isAndroid = str_contains($ua, 'android');

        if ($isIos) {
            return redirect()->away(self::IOS_URL);
        }

        if ($isAndroid) {
            return redirect()->away(self::ANDROID_URL);
        }

        // Desktop / unknown → show a nice landing page
        return view('app-download');
    }
}
