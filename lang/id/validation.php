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

    'accepted' => 'Isian :attribute harus diterima.',
    'accepted_if' => 'Isian :attribute harus diterima ketika :other adalah :value.',
    'active_url' => 'Isian :attribute bukan URL yang sah.',
    'after' => 'Isian :attribute harus tanggal setelah :date.',
    'after_or_equal' => 'Isian :attribute harus tanggal setelah atau sama dengan :date.',
    'alpha' => 'Isian :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Isian :attribute hanya boleh berisi huruf, angka, dan strip.',
    'alpha_num' => 'Isian :attribute hanya boleh berisi huruf dan angka.',
    'array' => 'Isian :attribute harus berupa sebuah array.',
    'ascii' => 'Isian :attribute hanya boleh berisi single-byte huruf and simbol.',
    'before' => 'Isian :attribute harus tanggal sebelum :date.',
    'before_or_equal' => 'Isian :attribute harus tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => 'Isian :attribute harus antara :min dan :max item.',
        'file' => 'Isian :attribute harus antara :min dan :max kilobytes.',
        'numeric' => 'Isian :attribute harus antara :min dan :max.',
        'string' => 'Isian :attribute harus antara :min dan :max karakter.',
    ],
    'boolean' => 'Isian :attribute harus berupa true atau false',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Kata sandi salah.',
    'date' => ':attribute bukan tanggal yang valid.',
    'date_equals' => ':attribute harus berisi tanggal yang sama dengan :date.',
    'date_format' => ':attribute tidak cocok dengan format :format.',
    'decimal' => 'Isian :attribute harus :decimal tempat desimal.',
    'declined' => 'Isian :attribute harus ditolak.',
    'declined_if' => 'Isian :attribute harus ditolak ketika :other adalah :value.',
    'different' => 'Isian :attribute dan :other harus berbeda.',
    'digits' => 'Isian :attribute harus berupa angka :digits.',
    'digits_between' => 'Isian :attribute harus antara angka :min dan :max.',
    'dimensions' => 'Isian :attribute harus merupakan dimensi gambar yang sah.',
    'distinct' => 'Isian :attribute memiliki nilai yang duplikat.',
    'doesnt_end_with' => 'Isian :attribute tidak boleh diakhiri dengan: :values.',
    'doesnt_start_with' => 'The :attribute tidak boleh dimulai dengan: :values.',
    'email' => 'Isian :attribute harus berupa alamat surel yang valid.',
    'ends_with' => 'Isian :attribute harus diakhiri dengan: :values.',
    'enum' => 'Isian :attribute yang dipilih tidak valid.',
    'exists' => 'Isian :attribute yang dipilih tidak valid.',
    'file'=> 'Isian :attribute harus berupa file.',
    'filled' => 'Isian :attribute wajib diisi.',
    'gt' => [
        'array'     => 'Isian :attribute harus memiliki lebih dari :value item.',
        'file'      => 'Isian :attribute harus lebih besar dari :value kilobytes.',
        'numeric'   => 'Isian :attribute harus lebih besar dari :value.',
        'string'    => 'Isian :attribute harus lebih besar dari :value karakter.',
    ],
    'gte' => [
        'array'     => 'Isian :attribute harus memiliki :value item atau lebih.',
        'file'      => 'Isian :attribute harus lebih besar dari atau sama dengan :value kilobytes.',
        'numeric'   => 'Isian :attribute harus lebih besar dari atau sama dengan :value.',
        'string'    => 'Isian :attribute harus lebih besar dari atau sama dengan :value karakter.',
    ],
    'image' => 'Isian :attribute harus berupa gambar.',
    'in' => 'Isian :attribute yang dipilih tidak valid.',
    'in_array' => 'Isian :attribute tidak terdapat dalam :other.',
    'integer' => 'Isian :attribute harus merupakan bilangan bulat.',
    'ip' => 'Isian :attribute harus berupa alamat IP yang valid.',
    'ipv4' => 'Isian :attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => 'Isian :attribute harus berupa alamat IPv6 yang valid.',
    'json' => 'Isian :attribute harus berupa JSON string yang valid.',
    'lowercase' => 'Isian :attribute harus huruf kecil.',
    'lt' => [
        'array'     => 'Isian :attribute harus memiliki kurang dari :value item.',
        'file'      => 'Isian :attribute harus kurang dari :value kilobytes.',
        'numeric'   => 'Isian :attribute harus kurang dari :value.',
        'string'    => 'Isian :attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'array'     => 'Isian :attribute tidak boleh lebih dari :value item.',
        'file'      => 'Isian :attribute harus kurang dari atau sama dengan :value kilobytes.',
        'numeric'   => 'Isian :attribute harus kurang dari atau sama dengan :value.',
        'string'    => 'Isian :attribute harus kurang dari atau sama dengan :value karakter.',
    ],
    'mac_address' => 'Isian :attribute harus MAC address yang sah.',
    'max' => [
        'array'   => 'Isian :attribute seharusnya tidak lebih dari :max item.',
        'file'    => 'Isian :attribute seharusnya tidak lebih dari :max kilobytes.',
        'numeric' => 'Isian :attribute seharusnya tidak lebih dari :max.',
        'string'  => 'Isian :attribute seharusnya tidak lebih dari :max karakter.',
    ],
    'max_digits' => 'Isian :attribute tidak boleh lebih dari :max digit.',
    'mimes' => 'Isian :attribute harus dokumen berjenis : :values.',
    'mimetypes' => 'Isian :attribute harus berupa file bertipe: :values.',
    'min' => [
        'array'   => 'Isian :attribute harus minimal :min item.',
        'file'    => 'Isian :attribute harus minimal :min kilobytes.',
        'numeric' => 'Isian :attribute harus minimal :min.',
        'string'  => 'Isian :attribute harus minimal :min karakter.',
    ],
    'min_digits' => 'Isian :attribute tidak boleh kurang dari :min digit.',
    'multiple_of' => 'Isian :attribute harus sebagnya :value kali lipat.',
    'not_in' => 'Isian :attribute yang dipilih tidak valid.',
    'not_regex' => 'Isian :attribute format tidak valid.',
    'numeric' => 'Isian :attribute harus berupa angka.',
    'password' => [
        'letters' => 'Isian :attribute harus mengandung setidaknya satu huruf.',
        'mixed' => 'Isian :attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => 'Isian :attribute harus mengandung setidaknya satu angka.',
        'symbols' => 'Isian :attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => 'Isian :attribute terlalu lemah. Mohon masukkan coba yang lain :attribute.',
    ],
    'present' => 'Isian :attribute wajib ada.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'Format isian :attribute tidak valid.',
    'required' => 'Isian :attribute wajib diisi.',
    'required_array_keys' => 'Isian :attribute wajib mengandung: :values.',
    'required_if' => 'Isian :attribute wajib diisi ketika :other adalah :value.',
    'required_if_accepted' => 'Isian :attribute wajib diisi ketika :other diterima.',
    'required_unless' => 'Isian :attribute wajib diisi kecuali :other memiliki nilai :values.',
    'required_with' => 'Isian :attribute wajib diisi ketika terdapat :values.',
    'required_with_all' => 'Isian :attribute wajib diisi ketika terdapat :values.',
    'required_without' => 'Isian :attribute wajib diisi ketika tidak terdapat :values.',
    'required_without_all' => 'Isian :attribute wajib diisi ketika tidak terdapat ada :values.',
    'same' => 'Isian :attribute dan :other harus sama.',
    'size' => [
        'array'   => 'Isian :attribute harus mengandung :size item.',
        'file'    => 'Isian :attribute harus berukuran :size kilobyte.',
        'numeric' => 'Isian :attribute harus berukuran :size.',
        'string'  => 'Isian :attribute harus berukuran :size karakter.',
    ],
    'starts_with' => 'Isian :attribute harus dimulai dengan: :values.',
    'string' => 'Isian :attribute harus berupa string.',
    'timezone' => 'Isian :attribute harus berupa zona waktu yang sah.',
    'unique' => 'Isian :attribute sudah ada sebelumnya.',
    'uploaded' => 'Isian :attribute gagal mengunggah.',
    'uppercase' => 'Isian :attribute harus dalam huruf besar.',
    'url' => 'Format isian :attribute tidak sah.',
    'ulid' =>  'Isian :attribute harus berupa ULID yang sah.',
    'uuid' =>  'Isian :attribute harus berupa UUID yang sah.',
    'valid_file' => 'Format :Jenis berkas yang anda unggah berbahaya.',
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
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'title' => 'judul',
        'content' => 'isi',
        'name' => 'nama'
    ],

];
