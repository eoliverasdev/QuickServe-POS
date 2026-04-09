<?php

return [
    'business_name' => env('TICKET_BUSINESS_NAME', 'LA CRESTA'),
    'address_line1' => env('TICKET_ADDRESS_LINE1', 'C/ Sant Andreu, 6'),
    'postal_city' => env('TICKET_POSTAL_CITY', '17846 - Mata'),
    'phone' => env('TICKET_PHONE', '972 57 34 03'),
    'nif_line' => env('TICKET_NIF_LINE', 'B17880782 - La Cresta Marlai S.L.'),
    'invoice_series' => env('TICKET_INVOICE_SERIES', '00020TB'),
    'invoice_number_pad' => (int) env('TICKET_INVOICE_NUMBER_PAD', 8),
    /** Numeració correlativa a BD (desactivar només en proves puntuals) */
    'assign_fiscal_number' => filter_var(env('TICKET_ASSIGN_FISCAL_NUMBER', true), FILTER_VALIDATE_BOOL),
    'paper_width_mm' => (int) env('TICKET_PAPER_WIDTH_MM', 80),
    'iva_reduced' => (float) env('TICKET_IVA_REDUCED', 10),
    'iva_general' => (float) env('TICKET_IVA_GENERAL', 21),
    /*
     * Si el nom del producte conté alguna d'aquestes cadenes (sense distingir majúscules),
     * la línia s'imputa a l'IVA general (p. ex. bosses). La resta usa l'IVA reduït.
     */
    'general_iva_keywords' => ['bossa', 'bosses', 'bag'],
];
