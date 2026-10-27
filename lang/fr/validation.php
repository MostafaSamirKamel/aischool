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

    'accepted' => ':attribute doit être accepté.',
    'accepted_if' => ':attribute doit être accepté lorsque :other est :value.',
    'active_url' => ':attribute n\'est pas une URL valide.',
    'after' => ':attribute doit être une date postérieure à :date.',
    'after_or_equal' => ':attribute doit être une date postérieure ou égale à :date.',
    'alpha' => ':attribute ne doit contenir que des lettres.',
    'alpha_dash' => ':attribute ne doit contenir que des lettres, des chiffres, des tirets et des underscores.',
    'alpha_num' => ':attribute ne doit contenir que des lettres et des chiffres.',
    'array' => ':attribute doit être un tableau.',
    'ascii' => ':attribute ne doit contenir que des caractères ASCII.',
    'before' => ':attribute doit être une date antérieure à :date.',
    'before_or_equal' => ':attribute doit être une date antérieure ou égale à :date.',
    'between' => [
        'array' => ':attribute doit contenir entre :min et :max éléments.',
        'file' => ':attribute doit être entre :min et :max kilo-octets.',
        'numeric' => ':attribute doit être entre :min et :max.',
        'string' => ':attribute doit contenir entre :min et :max caractères.',
    ],
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'can' => ':attribute contient une valeur non autorisée.',
    'confirmed' => 'La confirmation de :attribute ne correspond pas.',
    'contains' => ':attribute doit contenir :values.',
    'current_password' => 'Le mot de passe actuel est incorrect.',
    'date' => ':attribute n\'est pas une date valide.',
    'date_equals' => ':attribute doit être égal à la date :date.',
    'date_format' => ':attribute ne correspond pas au format :format.',
    'declined' => ':attribute doit être refusé.',
    'declined_if' => ':attribute doit être refusé lorsque :other est :value.',
    'different' => ':attribute et :other doivent être différents.',
    'digits' => ':attribute doit contenir :digits chiffres.',
    'digits_between' => ':attribute doit contenir entre :min et :max chiffres.',
    'dimensions' => ':attribute a des dimensions d\'image invalides.',
    'distinct' => 'Le champ :attribute a une valeur en double.',
    'doesnt_end_with' => ':attribute ne doit pas se terminer par l\'une des valeurs suivantes: :values.',
    'doesnt_start_with' => ':attribute ne doit pas commencer par l\'une des valeurs suivantes: :values.',
    'email' => ':attribute doit être une adresse e-mail valide.',
    'ends_with' => ':attribute doit se terminer par l\'une des valeurs suivantes: :values.',
    'enum' => 'Le :attribute sélectionné est invalide.',
    'exists' => 'Le :attribute sélectionné est invalide.',
    'extensions' => ':attribute doit avoir l\'une des extensions suivantes: :values.',
    'file' => ':attribute doit être un fichier.',
    'filled' => 'Le champ :attribute doit avoir une valeur.',
    'gt' => [
        'array' => ':attribute doit contenir plus de :value éléments.',
        'file' => ':attribute doit être supérieur à :value kilo-octets.',
        'numeric' => ':attribute doit être supérieur à :value.',
        'string' => ':attribute doit contenir plus de :value caractères.',
    ],
    'gte' => [
        'array' => ':attribute doit contenir :value éléments ou plus.',
        'file' => ':attribute doit être supérieur ou égal à :value kilo-octets.',
        'numeric' => ':attribute doit être supérieur ou égal à :value.',
        'string' => ':attribute doit contenir :value caractères ou plus.',
    ],
    'hex_color' => ':attribute doit être une couleur hexadécimale valide.',
    'image' => ':attribute doit être une image.',
    'in' => 'Le :attribute sélectionné est invalide.',
    'in_array' => ':attribute n\'existe pas dans :other.',
    'integer' => ':attribute doit être un entier.',
    'ip' => ':attribute doit être une adresse IP valide.',
    'ipv4' => ':attribute doit être une adresse IPv4 valide.',
    'ipv6' => ':attribute doit être une adresse IPv6 valide.',
    'json' => ':attribute doit être une chaîne JSON valide.',
    'list' => ':attribute doit être une liste.',
    'lowercase' => ':attribute doit être en minuscules.',
    'lt' => [
        'array' => ':attribute doit contenir moins de :value éléments.',
        'file' => ':attribute doit être inférieur à :value kilo-octets.',
        'numeric' => ':attribute doit être inférieur à :value.',
        'string' => ':attribute doit contenir moins de :value caractères.',
    ],
    'lte' => [
        'array' => ':attribute ne doit pas contenir plus de :value éléments.',
        'file' => ':attribute doit être inférieur ou égal à :value kilo-octets.',
        'numeric' => ':attribute doit être inférieur ou égal à :value.',
        'string' => ':attribute doit contenir :value caractères ou moins.',
    ],
    'mac_address' => ':attribute doit être une adresse MAC valide.',
    'max' => [
        'array' => ':attribute ne doit pas contenir plus de :max éléments.',
        'file' => ':attribute ne doit pas dépasser :max kilo-octets.',
        'numeric' => ':attribute ne doit pas être supérieur à :max.',
        'string' => ':attribute ne doit pas dépasser :max caractères.',
    ],
    'max_digits' => ':attribute ne doit pas contenir plus de :max chiffres.',
    'mimes' => ':attribute doit être un fichier de type: :values.',
    'mimetypes' => ':attribute doit être un fichier de type: :values.',
    'min' => [
        'array' => ':attribute doit contenir au moins :min éléments.',
        'file' => ':attribute doit être d\'au moins :min kilo-octets.',
        'numeric' => ':attribute doit être au moins :min.',
        'string' => ':attribute doit contenir au moins :min caractères.',
    ],
    'min_digits' => ':attribute doit contenir au moins :min chiffres.',
    'missing' => 'Le champ :attribute doit être manquant.',
    'missing_if' => 'Le champ :attribute doit être manquant lorsque :other est :value.',
    'missing_unless' => 'Le champ :attribute doit être manquant sauf si :other est :value.',
    'missing_with' => 'Le champ :attribute doit être manquant lorsque :values est présent.',
    'missing_with_all' => 'Le champ :attribute doit être manquant lorsque :values sont présents.',
    'multiple_of' => ':attribute doit être un multiple de :value.',
    'not_in' => 'Le :attribute sélectionné est invalide.',
    'not_regex' => 'Le format de :attribute est invalide.',
    'numeric' => ':attribute doit être un nombre.',
    'password' => [
        'letters' => ':attribute doit contenir au moins une lettre.',
        'mixed' => ':attribute doit contenir au moins une lettre majuscule et une lettre minuscule.',
        'numbers' => ':attribute doit contenir au moins un chiffre.',
        'symbols' => ':attribute doit contenir au moins un symbole.',
        'uncompromised' => 'Le :attribute donné est apparu dans une fuite de données. Veuillez choisir un autre :attribute.',
    ],
    'present' => 'Le champ :attribute doit être présent.',
    'present_if' => 'Le champ :attribute doit être présent lorsque :other est :value.',
    'present_unless' => 'Le champ :attribute doit être présent sauf si :other est :value.',
    'present_with' => 'Le champ :attribute doit être présent lorsque :values est présent.',
    'present_with_all' => 'Le champ :attribute doit être présent lorsque :values sont présents.',
    'prohibited' => 'Le champ :attribute est interdit.',
    'prohibited_if' => 'Le champ :attribute est interdit lorsque :other est :value.',
    'prohibited_unless' => 'Le champ :attribute est interdit sauf si :other est dans :values.',
    'prohibits' => 'Le champ :attribute interdit la présence de :other.',
    'regex' => 'Le format de :attribute est invalide.',
    'required' => 'Le champ :attribute est obligatoire.',
    'required_array_keys' => 'Le champ :attribute doit contenir des entrées pour: :values.',
    'required_if' => 'Le champ :attribute est obligatoire lorsque :other est :value.',
    'required_if_accepted' => 'Le champ :attribute est obligatoire lorsque :other est accepté.',
    'required_if_declined' => 'Le champ :attribute est obligatoire lorsque :other est refusé.',
    'required_unless' => 'Le champ :attribute est obligatoire sauf si :other est dans :values.',
    'required_with' => 'Le champ :attribute est obligatoire lorsque :values est présent.',
    'required_with_all' => 'Le champ :attribute est obligatoire lorsque :values sont présents.',
    'required_without' => 'Le champ :attribute est obligatoire lorsque :values n\'est pas présent.',
    'required_without_all' => 'Le champ :attribute est obligatoire lorsqu\'aucun de :values n\'est présent.',
    'same' => ':attribute et :other doivent correspondre.',
    'size' => [
        'array' => ':attribute doit contenir :size éléments.',
        'file' => ':attribute doit être de :size kilo-octets.',
        'numeric' => ':attribute doit être :size.',
        'string' => ':attribute doit contenir :size caractères.',
    ],
    'starts_with' => ':attribute doit commencer par l\'une des valeurs suivantes: :values.',
    'string' => ':attribute doit être une chaîne de caractères.',
    'timezone' => ':attribute doit être un fuseau horaire valide.',
    'unique' => ':attribute a déjà été pris.',
    'uploaded' => 'Le téléchargement de :attribute a échoué.',
    'uppercase' => ':attribute doit être en majuscules.',
    'url' => ':attribute doit être une URL valide.',
    'ulid' => ':attribute doit être un ULID valide.',
    'uuid' => ':attribute doit être un UUID valide.',

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
            'rule-name' => 'message personnalisé',
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

    'attributes' => [],
];
