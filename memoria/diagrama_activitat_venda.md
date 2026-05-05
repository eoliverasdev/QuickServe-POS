# Diagrama d'activitat de venda

Aquest diagrama representa el flux principal de venda directa (no encàrrec), incloent validacions de sessió, selecció de productes, comprovació d'estoc i tancament de cobrament.

```mermaid
flowchart TD
    A([Inici]) --> B{Usuari autenticat?}
    B -- No --> C[Mostrar pantalla de login]
    C --> D[Introduir credencials]
    D --> E{Credencials vàlides?}
    E -- No --> F[Mostrar error d'autenticació]
    F --> D
    E -- Sí --> G[Accedir a la pantalla de venda]
    B -- Sí --> G

    G --> H[Seleccionar treballador]
    H --> I[Obrir catàleg de productes]
    I --> J[Afegir producte a la comanda]
    J --> K{Afegir més productes?}
    K -- Sí --> I
    K -- No --> L[Calcular subtotal i total]

    L --> M{Estoc suficient per a totes les línies?}
    M -- No --> N[Mostrar error d'estoc]
    N --> I
    M -- Sí --> O[Seleccionar mètode de pagament]
    O --> P[Confirmar venda]

    P --> Q{Dades de comanda vàlides?}
    Q -- No --> R[Mostrar error de validació]
    R --> O
    Q -- Sí --> S[Crear comanda i línies]
    S --> T[Descomptar estoc]
    T --> U[Assignar número fiscal]
    U --> V[Marcar comanda com a Pagat]
    V --> W[Mostrar resum / tiquet]
    W --> X([Fi])
```



