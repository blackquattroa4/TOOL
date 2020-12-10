<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute 必須要接受.',
    'active_url'           => ':attribute 為不正確網址.',
    'after'                => ':attribute 必須在 :date 之後.',
    'after_or_equal'       => ':attribute 必須在 :date 或之後.',
    'alpha'                => ':attribute 只能有英文字母.',
    'alpha_dash'           => ':attribute 只能有英文字母, 數字, 及\'-\'.',
    'alpha_num'            => ':attribute 只能有英文字母, 及數字.',
    'array'                => ':attribute 必須是矩陣.',
    'before'               => ':attribute 必須在 :date 之前.',
    'before_or_equal'      => ':attribute 必須在 :date 或之前.',
    'between'              => [
        'numeric' => ':attribute 必須介於 :min 和 :max 之間.',
        'file'    => ':attribute 必須介於 :min 和 :max 千位元之間.',
        'string'  => ':attribute 必須介於 :min 和 :max 字母之間.',
        'array'   => ':attribute 必須介於 :min 和 :max 元素之間.',
    ],
    'boolean'              => ':attribute 必須是 true 或 false.',
    'confirmed'            => ':attribute 不一致.',
    'date'                 => ':attribute 為不正確日期.',
    'date_equals'          => ':attribute 必須是等於 :date 的日期',
    'date_format'          => ':attribute 不符合格式 :format.',
    'different'            => ':attribute 和 :other 必須不同.',
    'digits'               => ':attribute 必須是 :digits 位數.',
    'digits_between'       => ':attribute 必須介於 :min 和 :max 位數之間.',
    'dimensions'           => ':attribute 影像尺寸不正確.',
    'distinct'             => ':attribute 蘭未有重複數值.',
    'email'                => ':attribute 必須是正確的電子郵件地址.',
    'ends_with'            => ':attribute 必須以 :value 結尾',
    'exists'               => '選取的 :attribute 不正確.',
    'file'                 => ':attribute 必須是檔案.',
    'filled'               => ':attribute 欄位必填.',
    'gt' => [
        'numeric' => ':attribute 必須大於 :value',
        'file' => ':attribute 必須大於 :value 千字節',
        'string' => ':attribute 必須大於 :value 字',
        'array' => ':attribute 必須大於 :value 數組項',
    ],
    'gte' => [
        'numeric' => ':attribute 必須大於或等於 :value',
        'file' => ':attribute 必須大於或等於 :value 千字節',
        'string' => ':attribute 必須大於或等於 :value 字',
        'array' => ':attribute 必須大於或等於 :value 數組項',
    ],
    'image'                => ':attribute 必須是圖形檔.',
    'in'                   => '選取的 :attribute 不正確.',
    'in_array'             => ':attribute 欄位和 :other 不一致.',
    'integer'              => ':attribute 必須是整數.',
    'ip'                   => ':attribute 為不正確 IP 網址.',
    'ipv4'                 => ':attribute 必須是 IPv4 網址.',
    'ipv6'                 => ':attribute 必須是 IPv6 網址.',
    'json'                 => ':attribute 為不正確 JSON 字串.',
    'lt' => [
        'numeric' => ':attribute 必須小於 :value',
        'file' => ':attribute 必須小於 :value 千字節',
        'string' => ':attribute 必須小於 :value 字',
        'array' => ':attribute 必須小於 :value 數組項',
    ],
    'lte' => [
        'numeric' => ':attribute 必須小於或等於 :value',
        'file' => ':attribute 必須小於或等於 :value 千字節',
        'string' => ':attribute 必須小於或等於 :value 字',
        'array' => ':attribute 必須小於或等於 :value 數組項',
    ],
    'max'                  => [
        'numeric' => ':attribute 必須小於 :max.',
        'file'    => ':attribute 必須小於 :max 千位元.',
        'string'  => ':attribute 必須小於 :max 字母.',
        'array'   => ':attribute 必須小於 :max 元素.',
    ],
    'mimes'                => ':attribute 必須是: :values 格式檔案.',
    'mimetypes'            => ':attribute 必須是: :values 格式檔案.',
    'min'                  => [
        'numeric' => ':attribute 最少是 :min.',
        'file'    => ':attribute 最少是 :min 千位元.',
        'string'  => ':attribute 最少是 :min 字母.',
        'array'   => ':attribute 最少是 :min 元素.',
    ],
    'not_in'               => '挑揀 :attribute 不正確.',
    'not_regex'            => ':attribute 格式無效',
    'numeric'              => ':attribute 必須是數字.',
    'password'             => '密碼錯誤',
    'present'              => ':attribute 必填.',
    'regex'                => ':attribute 格式不正確.',
    'required'             => ':attribute 欄位必需填入.',
    'required_if'          => ':attribute 必填，當 :other 是 :value.',
    'required_unless'      => ':attribute 必填，除非 :other 是 :values.',
    'required_with'        => ':attribute 必填，當 :values 出現時.',
    'required_with_all'    => ':attribute 必填，當 :values 出現時.',
    'required_without'     => ':attribute 必填，當 :values 未出現時.',
    'required_without_all' => ':attribute 必填，當 :values 都未出現時.',
    'same'                 => ':attribute 和 :other 必須一致.',
    'size'                 => [
        'numeric' => ':attribute 必須是 :size.',
        'file'    => ':attribute 必須是 :size 千位元.',
        'string'  => ':attribute 必須是 :size 字母.',
        'array'   => ':attribute 必須包含 :size 元素.',
    ],
    'string'               => ':attribute 必須是字串.',
    'timezone'             => ':attribute 必須世既有時區.',
    'unique'               => ':attribute 重複.',
    'uploaded'             => ':attribute 上傳失敗.',
    'url'                  => ':attribute 格式不正確.',
    'uuid'                 => ':attribute 必須是有效的UUID',
    'less_than_or_equal_to' => ':attribute 必須小於或等於 :limit',
    'bin_duplication' => '倉位名稱重複',
    'bin_quantity'  => '數量超過庫存',
    'bin_availability' => '該倉位數量不足',


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
