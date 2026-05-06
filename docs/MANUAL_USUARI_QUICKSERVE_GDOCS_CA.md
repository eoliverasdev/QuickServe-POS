# Manual d'Usuari
## QuickServe

Versió: 1.0  
Idioma: Català  
Ús previst: Operativa diària TPV i administració

---

## Índex

1. Introducció  
2. Perfils d'usuari  
3. Preparació inicial del sistema  
4. Operativa TPV (dia a dia)  
5. Panell d'administració  
6. Incidències habituals i solucions  
7. Checklists operatius  
8. Bones pràctiques d'ús  
9. Glossari

---

## 1. Introducció

QuickServe és l'eina de treball per gestionar la venda al TPV, els encàrrecs i les tasques d'administració del negoci en un únic flux.

Aquest manual està pensat per resoldre el treball real del dia a dia, amb instruccions clares i directes.

Objectius principals:

- Estandarditzar la manera de treballar.
- Reduir errors en venda, cobrament i gestió.
- Facilitar la incorporació de personal nou.

---

## 2. Perfils d'usuari

### 2.1 Personal operatiu TPV

Responsable de:

- Iniciar sessió.
- Fer vendes directes.
- Crear encàrrecs.
- Cobrar o anul·lar encàrrecs pendents.

### 2.2 Personal d'administració

Responsable de:

- Entrar al panell amb PIN.
- Consultar resum i tancament de caixa.
- Gestionar categories, productes i treballadors.
- Revisar l'historial de vendes.

---

## 3. Preparació inicial del sistema

Aquest apartat només cal quan es configura un entorn nou o de prova.

### 3.1 Posada en marxa

1. Instal·la dependències del backend:
   - `composer install`
   - `npm install`
2. Configura l'entorn:
   - Copia `.env.example` a `.env`
   - Executa `php artisan key:generate`
3. Prepara base de dades:
   - `php artisan migrate`
   - `php artisan db:seed`
4. Arrenca serveis:
   - `composer dev`
5. Si uses Flutter en local:
   - `--dart-define=API_BASE_URL=http://127.0.0.1:8000/api`

### 3.2 Credencial de prova

- Correu: `admin@gmail.com`  
- Contrasenya: `password`

---

## 4. Operativa TPV (dia a dia)

### 4.1 Inici de sessió

Quan s'utilitza: a l'inici de torn.

Passos:

1. Introdueix correu i contrasenya.
2. Confirma l'accés.
3. Espera la càrrega de catàleg i treballadors.

Resultat esperat:

- Entrada correcta a la pantalla principal TPV.

### 4.2 Venda directa

Quan s'utilitza: venda immediata al mostrador.

Passos:

1. Afegeix productes al carret.
2. Revisa quantitats i import.
3. Obre el cobrament.
4. Selecciona treballador.
5. Tria mètode de pagament.
6. Confirma la venda.

Resultat esperat:

- Venda registrada com a pagada.
- Stock actualitzat.

### 4.3 Crear un encàrrec

Quan s'utilitza: comanda per recollir més tard.

Passos:

1. Afegeix productes al carret.
2. Marca l'opció d'encàrrec.
3. Introdueix hora de recollida (si cal).
4. Introdueix nom del client (si cal).
5. Desa l'encàrrec.

Resultat esperat:

- Encàrrec guardat com a pendent.
- Número de recollida assignat.

### 4.4 Consultar encàrrecs pendents

Quan s'utilitza: seguiment d'encàrrecs del dia.

Accions disponibles:

- Cobrar
- Modificar
- Anul·lar

### 4.5 Cobrar un encàrrec pendent

Quan s'utilitza: quan el client recull.

Passos:

1. Obre el llistat d'encàrrecs pendents.
2. Selecciona l'encàrrec.
3. Tria mètode de pagament.
4. Assigna treballador.
5. Indica bosses (si aplica).
6. Confirma cobrament.

Resultat esperat:

- Estat final: pagat.
- Import final actualitzat.

### 4.6 Anul·lar un encàrrec

Quan s'utilitza: cancel·lació confirmada.

Passos:

1. Obre l'encàrrec pendent.
2. Selecciona l'opció d'anul·lació.
3. Confirma l'acció.

Resultat esperat:

- L'encàrrec s'elimina del llistat de pendents.
- El stock es restaura.

---

## 5. Panell d'administració

### 5.1 Accés amb PIN

Passos:

1. Des del TPV, obre accés a administració.
2. Introdueix PIN.
3. Valida l'entrada.

Resultat esperat:

- Accés a les seccions de gestió.

### 5.2 Resum

Permet consultar:

- Indicadors principals de venda.
- Productes destacats.
- Tendències de rendiment.

### 5.3 Tancament de caixa

Passos:

1. Obre la secció de tancament.
2. Revisa imports totals i desglossaments.
3. Valida dades abans de tancar jornada.

Resultat esperat:

- Visió clara i coherent de la caixa del període.

### 5.4 Categories

Accions:

- Crear categoria.
- Editar categoria.
- Eliminar categoria.

Recomanació:

- Mantenir noms curts i consistents.

### 5.5 Productes

Accions:

- Crear producte.
- Editar preu, stock, estat i atributs.
- Eliminar producte obsolet.

Resultat esperat:

- El TPV reflecteix el catàleg actualitzat.

### 5.6 Treballadors

Accions:

- Alta i baixa de personal.
- Activar o desactivar perfils.
- Assignar PIN de 4 dígits.

Regla important:

- Només una persona pot tenir PIN d'administració al mateix temps.

### 5.7 Historial de vendes

Permet:

- Filtrar per estat, pagament, dates i treballador.
- Consultar detall de comandes.
- Eliminar registres quan el procediment intern ho requereixi.

---

## 6. Incidències habituals i solucions

### 6.1 No es pot iniciar sessió

- Revisa correu i contrasenya.
- Verifica que el servei estigui actiu.
- Comprova la URL de connexió.

### 6.2 No carreguen productes o treballadors

- Recarrega la pantalla.
- Comprova connexió de xarxa.
- Verifica que hi hagi elements actius.

### 6.3 Error de stock

- Redueix quantitat.
- Revisa stock des de productes.
- Torna a intentar la venda.

### 6.4 PIN rebutjat

- Revisa que tingui 4 dígits.
- Confirma qui té el PIN actiu.
- Actualitza el PIN si cal.

### 6.5 Dades inconsistents

- Refresca l'aplicació.
- Revisa l'historial de la comanda.
- Si persisteix, reporta la incidència amb data, hora i pas exacte.

---

## 7. Checklists operatius

### 7.1 Checklist d'obertura

- Sistema en marxa.
- Catàleg visible.
- Treballadors carregats.
- Cobrament operatiu.

### 7.2 Checklist de tancament

- Caixa revisada.
- Encarrecs pendents revisats.
- Incidències registrades.

---

## 8. Bones pràctiques d'ús

- Confirmar sempre treballador i mètode de pagament abans de tancar una venda.
- Evitar acumular encàrrecs sense dades mínimes de seguiment.
- Revisar tancament cada final de jornada.
- Mantenir catàleg net: productes actius i informació actualitzada.
- Documentar qualsevol incidència recurrent.

---

## 9. Glossari

- TPV: Terminal de punt de venda.
- Encàrrec pendent: comanda reservada encara no cobrada.
- Tancament de caixa: resum econòmic del període de treball.
- PIN d'administració: codi de 4 dígits per accedir al panell de gestió.
