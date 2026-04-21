# Memòria del Projecte Final - Secció 1

## Portada

- **Nom i cognoms:** Eduard Oliveras Guerrero
- **Estudis:** 2DAW
- **Centre:** IES Rafael Campalans
- **Títol del projecte:** Quickserve
- **Data:** 21 d'abril de 2026

---

## 1. Introducció i objectius

### 1.1 Descripció general

Quickserve és una aplicació web orientada a la gestió de comandes en una rostisseria. El projecte neix per digitalitzar processos que habitualment es fan en paper i que poden provocar errors, pèrdua de temps i dificultats en el seguiment de l'estat de les comandes.

La plataforma centralitza la gestió del catàleg de productes, la creació i seguiment de comandes, i la coordinació del treball diari de l'equip. A nivell tècnic, el projecte es basa en un backend amb Laravel i una base de dades MySQL, i contempla també la integració amb clients Flutter per a diferents plataformes.

### 1.2 Motivació per fer el projecte

La motivació principal del projecte és una necessitat real detectada en un entorn de treball quotidià. Actualment, en la rostisseria on treballa l'autor, els encàrrecs i part de la gestió diària encara es fan en paper.

Aquest sistema manual genera limitacions operatives: costa més consultar informació ràpidament, hi ha risc d'errors humans i es dificulta tenir un control ordenat del servei. Per aquest motiu, es planteja Quickserve com una eina digital útil i aplicable en un cas real.

### 1.3 Finalitats del projecte

Els objectius principals de Quickserve són:

1. Substituir la gestió manual en paper per una gestió digital centralitzada.
2. Millorar l'organització de comandes i encàrrecs durant el servei.
3. Reduir errors en la presa i preparació de comandes.
4. Fer que la solució sigui usable en un entorn real de rostisseria.
5. Construir una base escalable per afegir funcionalitats futures.

### 1.4 Alternatives a la construcció

En la fase inicial es van valorar dues vies:

- Mantenir el sistema actual basat en paper.
- Desenvolupar una solució pròpia adaptada al flux real de treball.

Tot i que mantenir el paper té cost inicial nul, presenta baixa traçabilitat i més risc d'errors operatius. Per això s'opta per una eina pròpia, ja que permet adaptar el sistema a les necessitats reals de la rostisseria i evolucionar-lo progressivament.

### 1.5 Tria de llenguatges i SGBD

Les tecnologies principals escollides són:

- **Backend:** PHP 8.2 amb Laravel 12.
- **Base de dades (SGBD):** MySQL.
- **Autenticació API:** Laravel Sanctum.
- **Clients complementaris:** Flutter (web, windows, android), segons el contracte d'API del projecte.

Aquesta tria respon a criteris de productivitat, mantenibilitat, ecosistema de llibreries i facilitat per construir una arquitectura preparada per escenaris reals.

### 1.6 Evolució de l'abast del projecte

L'abast inicial del projecte era construir només una secció enfocada a la gestió d'encàrrecs des d'una tauleta. Durant el desenvolupament es va detectar una limitació clau: no era viable integrar aquesta part amb l'aplicació que l'empresa utilitza actualment.

Davant d'aquesta situació, es va redefinir l'abast per construir una aplicació completa capaç de gestionar l'operativa global del negoci. Aquest canvi ha incrementat la complexitat del projecte, però també el seu valor real d'implantació.

---

## 2. Pla d'empresa

### 2.1 Estudi de mercat (esborrany inicial)

El sector de restauració de proximitat necessita eines de digitalització simples, de cost controlat i fàcils d'adoptar per equips petits. Molts negocis continuen amb processos manuals o amb eines massa genèriques que no s'ajusten al seu flux de treball.

Quickserve es posiciona com una solució enfocada a establiments com rostisseries i negocis similars, amb funcionalitats específiques per a comandes, encàrrecs i operativa diària.

### 2.2 Anàlisi D.A.F.O. (esborrany inicial)

**Debilitats**
- Projecte inicial desenvolupat en el marc acadèmic.
- Recursos limitats per a comercialització immediata.

**Amenaces**
- Existència de TPV comercials consolidats.
- Resistència al canvi en negocis acostumats al paper.

**Fortaleses**
- Solució nascuda d'una necessitat real i validable en entorn real.
- Control total sobre funcionalitats i evolució del producte.

**Oportunitats**
- Tendència general de digitalització de petits negocis.
- Possibilitat d'adaptar el producte a altres establiments similars.

### 2.3 Pressupost del projecte

#### 2.3.1 Anàlisi del hardware necessari

Per posar Quickserve en funcionament en un entorn real, es considera el maquinari següent:

- PC amb pantalla tàctil (x2): 900 EUR/unitat (ja disponibles a l'empresa).
- Tauleta (x1): 250 EUR (aportada per l'autor del projecte).
- Impressora de tiquets (x1): 180 EUR (ja disponible a l'empresa).

**Cost total estimat de maquinari:** 2.230 EUR.

**Inversió addicional necessària per desplegar el projecte actualment:** 250 EUR (només la tauleta), ja que la resta de dispositius ja existeixen.

#### 2.3.2 Anàlisi del software necessari

Programari principal previst:

- Entorn de desenvolupament i servidor local.
- Framework Laravel i dependències associades.
- Base de dades MySQL.
- Eines de control de versions i seguiment.

El projecte s'ha desenvolupat íntegrament amb tecnologies gratuïtes i de codi obert, de manera que el cost de llicències és 0 EUR. Funcionalment, aquesta tria permet construir una solució robusta, mantenible i sense dependència de software comercial de pagament.

### 2.4 Finançament

El projecte és autofinançat per l'autor. En cas d'aparèixer algun cost addicional de desplegament o manteniment, aquest serà assumit personalment.

A més del cost directe de maquinari, es considera el cost d'oportunitat de les hores invertides en desenvolupament. Com a estimació econòmica, si es valoren 180 hores de treball a 15 EUR/h, el cost teòric de desenvolupament és:

- 2.700 EUR (cost estimat de dedicació tècnica).

Aquest valor no implica una despesa real pagada a tercers, però és útil per quantificar l'esforç del projecte.

---

## Estat de la Secció 1

La Secció 1 queda tancada amb els apartats obligatoris de portada, introducció i objectius, alternatives, tria tecnològica i pla d'empresa inicial (mercat, D.A.F.O., pressupost i finançament).
