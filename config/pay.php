<?php

return [
    'alipay' => [
        'app_id'         => '2021000116685589',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlmvh7lvZgWOZOHvWTSAEeU+IOhFtWpQn+NTNaJyFYpiikJ1Teb4PkjVSfwnyQI3CmrbtEVzTqII0X5P+89SSY3ot2oysOfk8i9cpEtIqUUpUlkM5OdP/uOvYnQjJWR8BE1Ai2C6/p01ks2QUzdduBnSBLAU1slnf+ZGgtVrh6JiPUBzFYl+ovBQtoIqodCHIoChmuCY8U8VIFUImsN+SLxq9uOZpd0WryRRxEEbFgNDR5C/LAYkuNUg4LYrNblpoPYM/xeJPVX4LsCC5ymUWYfL8s/VKRF26z1VbmtsNaTXVXLDVMetv/vf/LY0OSN7mGbFa2N8FbSg0PbE803u/nwIDAQAB',
        'private_key'    => 'MIIEowIBAAKCAQEAhT4hIW3NiDz85cYNKriMsFBHa9baPHl70hiCbiqxI3Bm+4IZ3xwy032bHTAuRsrOcMD4U/CDmYzEq8l68crDNXE8J5qGp4auLvZsJ6PG1nshukzHXmwRneffkMWHd/htrkchmJGVym/nnJ5LX0/N9FQD4Ja0v9d0wvYAx8R08UveglKr7bVEB32vCFiBxgFY51Ni9SD9M3Ri/7h1rlnsbd4mlRnt8QNg1zD9pCSfl434v3rrAWUcwhBwzTUjOE5O7kFN0K4dbTop4FVs5ItsF8LOGYECQ4EPh1ZIY8rfHT/XsOWHIy7jMHXcmouUse/Bhhyg68qq4joeisRvt2fUdQIDAQABAoIBABd67GG0tNyGSta/AQD/RqOncf6Z/RldGynZ8doyIccp/qvhsGd106sbXmNVY0JMaPbLurcoEjjT6rCPL+A+KApZ6wzbmVEaVcWm3UZbASY4yqfG8fc8uhoGi4o6lPXaTJHCrTPyq9/huoIKDyQ7rwKYb5p7VFzePhBBQ7/wad8/iGk+5uY6LJ8g6tJHdEsoNayGm8hOzNAWgVAc6do39s+0ta9kHBaq870q6VxGU/zTxrxWF7hgFcUc94T7coZI25t49rPLEwxtPLAduHfZ+7qcDC/w0QjjZsh0SlpfcdCi4kiZSlqUGrQnYHmkdGMVdYTkgvKlgTuX2CHfsB/ReF0CgYEA+VnHm/Cj9KAoqVL76ESHu0Zyz2cyMmSLba7JftLlazqtw/B1qI/+nI6sQrWcILNCOXqsDZfmwD0K2DDgd06xuB6m/ewCy6mb6hfUVUcc2+iKLqXQ25VcHooQ39rjw6uy2oBHq2WpjZWh59gvVFFnguxuZeSiciHzjhDtk6Nf+dMCgYEAiMu5uDTnODBNmP/9bS55Cww5FH+UBJ7povRF1dOLtlcWSWpe5tJrzzVrNHdUPlykgtzfnxeGyAtmibz14G5E7F2y9Q+FmHxvebfqK5itotAy3TUza9LookHlkKQsBLifekOqwqDwK5f9JkdQ7HzGkCo6JghpGwBmwY8DIEXqA5cCgYEAwWISv+sKjS8lOeTk/4ceqWyCoD3NnW2DAQa8uMJBMT9qAntcFOXQNoUkLfyBYI8F/mQYSdaUHGV7Ip4NiBodFMmZA3dl16PsCsp+X8DuLdMUUAdDivk0X27+lU5CtL9Vz8YLT29h5y+0SIXOdxtHJy/MkvOV1ORl6rlQN99OEQcCgYAMHnBPn3A0jCLzwQoEmC9lCSotZLgKSMHOC/H75YflGYYB7wiFTnL6onLi7Qdrlu2EImk1QPJR2qC05yapQVudzRdDmqJtRejiUabPQfFwER3QdxwdG0w8w7XAwKqdqHWIcWI5Z+SfGBH3SC4lJVC6COOp5ujY2YpA75iJTWhT9QKBgFlDf8UV7c4gMDqhcNIz6H10I3U+XTkyRWPHves6seXW7+QNuwH02LcY0Okw0SZPMPcFQ9AAFzoUSMLZ6dSGM3XZ32lnMto9oSkYO8mwUW+VfI+uh63lQnN8QUctXr9kfs7fnEH+VlRrWMIYkornO8D0Nw928cxlewEQ5Ra5/qSV',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];