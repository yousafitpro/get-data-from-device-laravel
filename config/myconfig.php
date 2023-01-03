<?php
return [
    'MT' =>[
        'oid'=>env('MT_OID', 'none'),
        'key'=>env('MT_KEY', 'none'),
        'url'=>env('MT_URL', 'none'),
    ],
    'PL' =>[
        'cid'=>env('PL_CID', 'none'),
        'key'=>env('PL_KEY', 'none'),
        'url'=>env('PL_URL', 'none'),
    ],
    'Twilio' =>[
        'sid'=>env('Twilio_SID', 'none'),
        'token'=>env('Twilio_Token', 'none'),
        'from'=>env('Twilio_From', 'none'),
    ],
    'Quickbook' =>[
        'url'=>env('Quickbook_URL', 'none'),
        'ci'=>env('Quickbook_CI', 'none'),
        'sk'=>env('Quickbook_SK', 'none'),
        'ver'=>env('Quickbook_Ver', 'none'),
    ],
    'Xero' =>[
        'url'=>env('Xero_URL', 'none'),
        'ci'=>env('Xero_CI', 'none'),
        'sk'=>env('Xero_SK', 'none'),
    ],
       'Freshbook' =>[
    'url'=>env('Freshbook_URL', 'none'),
    'ci'=>env('Freshbook_CI', 'none'),
    'sk'=>env('Freshbook_SK', 'none'),
],
       'RLZ' =>[
    'if'=>env('RLZ_IF', 'none'),
    'key'=>env('RLZ_KEY', 'none'),
    'url'=>env('RLZ_URL', 'none'),
    'auth_url'=>env('RLZ_AUTH_URL', 'none'),

],
    'AP' =>[
        'url'=>env('Apt_url', 'none'),
        'key'=>env('Apt_api_key', 'none'),
        'secret'=>env('Apt_secret_key', 'none'),
        'token'=>env('Apt_token', 'none'),
        'sender_id'=>env('Apt_sender_id', 'none'),

    ],
    'TP' =>[
        'url'=>env('Telpay_url', 'none'),
        'username'=>env('Telpay_username', 'none'),
        'password'=>env('Telpay_password', 'none'),

    ],
    'Square' =>[
        'url'=>env('Square_url', 'none'),
        'token'=>env('Square_token', 'none'),
    ],
    'Converge' =>[
    'url'=>env('converge_url', 'none'),
    'mid'=>env('converge_m_id', 'none'),
    'mpin'=>env('converge_m_pin', 'none'),
    'uid'=>env('converge_u_id', 'none'),
    'script_url'=>env('converge_script_url', 'none'),
],
    'VGS' =>[
        'key'=>env('vgs_key', 'none'),
        'env'=>env('vgs_env', 'none'),
    ],
    'App' =>[
        'api_key'=>env('api_key', 'none'),
        'zpayd_url'=>env('zpayd_url', 'none'),
        'zpayd_merchant_url'=>env('zpayd_merchant_url', 'none'),
    ],
    'Recap' =>[
    'site_key'=>env('Recap_site_key', 'none'),
    'secret_key'=>env('Recap_secret_key', 'none'),
],
'google' =>[
    'key'=>env('google_key', 'none'),
],
    'Airwallex' =>[
        'url'=>env('Airwallex_url', 'none'),
        'cid'=>env('Airwallex_client_id', 'none'),
        'key'=>env('Airwallex_api_key', 'none'),
    ]
];
