# Manual d'usuari QuickServe (CAT)

## 1. Objectiu del manual

Aquest manual explica com utilitzar QuickServe en el dia a dia per a:

- Personal d'operativa de TPV (venda i encarrecs).
- Personal d'administracio (cataleg, equip, caixa i historial).

Esta pensat per a us real de botiga, amb passos curts i accionables.

## 2. Perfils d'us i responsabilitats

### 2.1 Operativa TPV

Pot fer:

- Iniciar sessio.
- Fer vendes directes.
- Crear i gestionar encarrecs.
- Cobrar encarrecs pendents.
- Tornar al flux de treball de caixa.

### 2.2 Administracio

Pot fer:

- Entrar al panell d'administracio amb PIN valid.
- Consultar resum i tancament de caixa.
- Gestionar categories, productes i treballadors.
- Revisar historial de vendes.

## 3. Posada en marxa minima (entorn de prova)

Si encara no tens l'aplicacio preparada:

1. Installa dependencies backend:
   - `composer install`
   - `npm install`
2. Configura entorn:
   - copia `.env.example` a `.env`
   - executa `php artisan key:generate`
3. Prepara base de dades:
   - `php artisan migrate`
   - `php artisan db:seed`
4. Arrenca backend + utilitats:
   - `composer dev`
5. Per Flutter, defineix la URL API local en executar:
   - `--dart-define=API_BASE_URL=http://127.0.0.1:8000/api`

Credencial de prova (seed):

- Email: `admin@gmail.com`
- Password: `password`

## 4. Flux operatiu TPV

### 4.1 Inici de sessio

Quan s'utilitza:

- Al començament del torn o en obrir l'aplicacio.

Passos:

1. Introdueix email i contrasenya.
2. Confirma l'acces.
3. Espera la carrega inicial de cataleg i treballadors.

Resultat esperat:

- S'obre la pantalla principal del TPV i es pot operar.

Errors frequents:

- Credencials incorrectes: revisa email/contrasenya.
- No es carrega el TPV: comprova connectivitat amb API.

### 4.2 Venda directa

Quan s'utilitza:

- Venda immediata al mostrador.

Passos:

1. Selecciona productes del cataleg.
2. Revisa quantitats al carret.
3. Obre la pantalla de cobrament.
4. Assigna treballador.
5. Selecciona metode de pagament (efectiu o targeta).
6. Confirma la venda.

Resultat esperat:

- Venda guardada amb estat pagat.
- El stock es redueix.
- Es mostra confirmacio de venda.

Errors frequents:

- Stock insuficient: redueix quantitat o elimina producte.
- Treballador no seleccionat: assigna un treballador abans de confirmar.

### 4.3 Creacio d'encarrec

Quan s'utilitza:

- Comanda per recollida posterior.

Passos:

1. Afegeix productes al carret.
2. Marca opcio d'encarrec.
3. Introdueix hora de recollida (opcional segons cas).
4. Introdueix nom client (opcional segons cas).
5. Guarda l'encarrec.

Resultat esperat:

- Encàrrec guardat amb numero de recollida.
- Estat pendent.
- Apareix a la llista d'encarrecs pendents del dia.

Errors frequents:

- Carret buit: afegeix almenys un producte.
- Error de guardat: reintenta i verifica connexio.

### 4.4 Gestio d'encarrecs pendents

Quan s'utilitza:

- Seguiment d'encarrecs abans del cobrament final.

Accions disponibles:

- Cobrar encarrec.
- Modificar encarrec.
- Anullar encarrec.

Resultat esperat:

- Cobrat: passa a pagat i surt de pendents.
- Anullat: s'elimina i es restaura stock.

### 4.5 Cobrament d'un encarrec pendent

Quan s'utilitza:

- Quan el client recull i paga.

Passos:

1. Obre la llista de pendents.
2. Selecciona l'encarrec.
3. Tria metode de pagament.
4. Assigna treballador.
5. Indica nombre de bosses (si aplica).
6. Confirma cobrament.

Resultat esperat:

- Estat final pagat.
- Numeracio fiscal assignada.
- Import final actualitzat (incloent bosses si cal).

Errors frequents:

- Producte de bossa no trobat: revisar configuracio de cataleg.
- Dades de cobrament incompletes: comprovar metode i treballador.

## 5. Flux d'administracio

### 5.1 Acces al panell admin per PIN

Quan s'utilitza:

- Per entrar a funcionalitats de gestio.

Passos:

1. Des del TPV, obre l'acces a administracio.
2. Introdueix PIN.
3. En validacio correcta, entra al panell admin.

Resultat esperat:

- Es mostren seccions: Resum, Caixa, Categories, Productes, Treballadors, Historial.

Errors frequents:

- PIN incorrecte: reintenta.
- PIN no assignat: cal configurar-lo a Treballadors.

### 5.2 Resum (Dashboard)

Quan s'utilitza:

- Seguiment operatiu diari i tendencia recent.

Permet consultar:

- KPIs de vendes.
- Top productes.
- Distribucio temporal.
- Repartiment per metode de pagament.

### 5.3 Tancament de caixa

Quan s'utilitza:

- Tancament de torn o fi de jornada.

Passos:

1. Entra a "Tancament de Caixa".
2. Revisa imports agregats del periode.
3. Verifica totals i desglossaments.
4. Registra el tancament segons protocol intern.

Resultat esperat:

- Xifres coherents de caixa i impostos per a control intern.

### 5.4 Gestio de categories

Quan s'utilitza:

- Alta o manteniment de families de producte.

Passos habituals:

1. Crear categoria nova (nom i color).
2. Editar categoria existent.
3. Eliminar categoria si ja no s'utilitza.

Bona practica:

- Mantenir noms curts i consistents per facilitar el treball a TPV.

### 5.5 Gestio de productes

Quan s'utilitza:

- Crear o actualitzar cataleg de venda.

Passos habituals:

1. Crear producte (nom, preu, stock, categories, etc.).
2. Marcar si esta actiu/inactiu.
3. Gestionar atributs (sense gluten, descripcio, imatge).
4. Editar o eliminar productes obsolets.

Resultat esperat:

- El TPV reflecteix els canvis del cataleg.

### 5.6 Gestio de treballadors

Quan s'utilitza:

- Alta/baixa d'equip i control d'acces admin.

Passos habituals:

1. Crear treballador.
2. Activar/desactivar segons estat laboral.
3. Assignar PIN de 4 digits (opcional).

Regla important:

- Nomes un treballador pot tenir PIN admin al mateix temps.

### 5.7 Historial de vendes

Quan s'utilitza:

- Consulta i control d'operacions passades.

Funcions:

- Filtrar per estat, pagament, treballador i dates.
- Cercar comandes.
- Obrir detall de comanda.
- Eliminar registres (si la politica interna ho permet).

## 6. Incidencies frequents i resolucio rapida

### 6.1 No es pot iniciar sessio

- Verifica credencials.
- Comprova que backend esta en marxa.
- Revisa que la URL API sigui la correcta.

### 6.2 El TPV no carrega cataleg o treballadors

- Comprova connexio de xarxa.
- Reintenta des de la pantalla.
- Verifica que hi ha categories/productes/treballadors actius.

### 6.3 Error de stock en venda

- Redueix quantitat.
- Revisa stock del producte a administracio.
- Torna a intentar la venda.

### 6.4 PIN admin rebutjat

- Revisa que el PIN sigui de 4 digits.
- Confirma qui es el titular actual del PIN.
- Si cal, actualitza PIN des de Treballadors.

### 6.5 Inconsistencies de dades

- Refresca pantalla (recarrega).
- Valida que no hi hagi duplicats de comanda.
- Si persisteix, reporta incidencia amb hora i pas executat.

## 7. Checklist operatiu recomanat

### 7.1 Obertura

- Backend/API disponibles.
- Cataleg visible.
- Treballadors carregats.
- Flux de cobrament comprovat amb una operacio de test (si protocol intern ho exigeix).

### 7.2 Tancament

- Revisar caixa.
- Revisar encarrecs pendents no cobrats.
- Confirmar que no queden incidencies obertes.

## 8. Limitacions conegudes (per operacio segura)

- El control de permisos admin a nivell backend no esta completament desacoblat per rol en tots els casos.
- Existeixen rutes web legacy que poden no reflectir al 100% el flux modern Flutter.
- Si no es defineix `API_BASE_URL` a Flutter, pot utilitzar una URL remota per defecte.

## 9. Glossari curt

- TPV: Terminal punt de venda.
- Encarrec pendent: comanda reservada pendent de cobrament.
- Tancament de caixa: resum econòmic del periode operatiu.
- PIN admin: codi de 4 digits per accedir a panell administratiu.
