# Diagrama Gantt del projecte Quickserve

```mermaid
gantt
    title Cronograma complet del desenvolupament (des. 2025 - abr. 2026)
    dateFormat  YYYY-MM-DD
    axisFormat  %d/%m

    section Fase inicial
    Diagnòstic problema i definició MVP (~3,5 h)           :a1, 2025-12-02, 4d
    Elecció stack i model de dades inicial (~4,5 h)        :a2, 2025-12-09, 6d
    Setup, migracions i relacions Eloquent (~9,5 h)        :a3, 2025-12-20, 22d

    section Nucli funcional
    Autenticació Sanctum + catàleg (~5 h)                  :b1, 2026-01-12, 3d
    Creació vendes, línies i totals (~7 h)                 :b2, 2026-01-16, 4d
    Control d'estoc i transaccions BD (~5,5 h)             :b3, 2026-01-21, 4d
    Disseny i alta d'encàrrecs (~7 h)                      :b4, 2026-01-28, 4d

    section Flux avançat d'encàrrecs
    Llista pendents i cobrament encàrrecs (~6 h)           :c1, 2026-02-04, 5d
    Bossa en cobrament i cancel·lació (~7 h)               :c2, 2026-02-11, 4d
    Cas mig pollastre i replantejament abast (~4,5 h)      :c3, 2026-02-18, 5d

    section Mòdul administratiu i millores
    Administració categories i productes (~7 h)            :d1, 2026-02-26, 4d
    Pujada imatges i gestió treballadors (~5,5 h)          :d2, 2026-03-05, 5d
    Històric comandes + dashboard KPI (~8 h)               :d3, 2026-03-13, 5d
    Numeració fiscal automàtica (~3,5 h)                   :d4, 2026-03-22, 1d

    section Qualitat i tancament
    Reforç validacions + regressió encàrrecs (~6,5 h)      :e1, 2026-03-29, 7d
    Estabilització estoc + neteja codi (~5,5 h)            :e2, 2026-04-10, 6d
    Documentació API + traçabilitat requisits (~4 h)       :e3, 2026-04-18, 3d
    Preparació final del lliurament (~2 h)                 :milestone, e4, 2026-04-21, 1d
```



## Resum d'esforç

- Hores totals aproximades: **101,5 h**
- Període global: **2 desembre 2025 - 21 abril 2026**
- Estructura del cronograma: fase inicial, nucli funcional, flux avançat, administració i tancament

