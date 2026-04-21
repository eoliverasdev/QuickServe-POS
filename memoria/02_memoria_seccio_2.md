# Memòria del Projecte Final - Secció 2

## Anàlisi, disseny i construcció del projecte

### 1. Fases del projecte (durada estimada i durada real)

Per estructurar el desenvolupament de Quickserve, es va plantejar una seqüència de fases iterativa, combinant planificació, implementació i validació contínua.

| Fase | Període estimat | Període real | Resultat principal |
|---|---|---|---|
| Ideació i planificació inicial | Desembre | Desembre | Definició del problema real i enfoc inicial del producte |
| Desenvolupament inicial | Gener | Gener | Primera base funcional del sistema |
| Proves i ajustos iteratius | Gener - Abril | Gener - Abril | Correcció d'errors i millores d'usabilitat/flux |
| Ampliació d'abast i consolidació | Febrer - Abril | Febrer - Abril | Pas d'una secció parcial a una app de gestió completa |

L'estimació inicial contemplava un abast més reduït (mòdul d'encàrrecs en tauleta). La durada real i l'abast final es van ampliar en detectar la necessitat de construir una solució integral per poder substituir el procés actual de l'empresa.

### 2. Temporització

La temporització s'ha basat en cicles curts:

- Planificació de tasques concretes.
- Implementació incremental de funcionalitats.
- Proves funcionals en paral·lel al desenvolupament.
- Revisió i reajust en funció dels resultats.

Aquesta metodologia ha permès avançar de manera contínua i adaptar l'abast a les necessitats reals detectades durant el projecte.

### 3. Diagrama de Gantt (recursos i terminis)

Representació simplificada del calendari de treball:

| Tasca / Mes | Des | Gen | Feb | Mar | Abr |
|---|---|---|---|---|---|
| Planificació inicial | X |  |  |  |  |
| Arquitectura i modelat inicial | X | X |  |  |  |
| Desenvolupament backend i API |  | X | X | X | X |
| Desenvolupament interfície / flux operatiu |  | X | X | X | X |
| Proves funcionals |  | X | X | X | X |
| Correccions i millores |  | X | X | X | X |
| Preparació memòria i entrega |  |  |  | X | X |

**Recursos principals:**
- 1 desenvolupador (autor del projecte).
- Entorn real de rostisseria per validar fluxos de treball.
- Equipament disponible a l'empresa (PC tàctils i impressora) + tauleta de suport.

### 4. Planificació i seguiment (Jira i GitHub)

El seguiment del projecte s'ha fet amb:

- **Jira:** planificació de tasques, priorització, seguiment de l'estat i control del progrés.
- **GitHub:** control de versions, registre de canvis mitjançant commits i còpia remota de seguretat amb push.

La combinació d'ambdues eines ha facilitat tenir traçabilitat tant funcional (què s'havia de fer i en quin estat està) com tècnica (quins canvis s'han aplicat al codi).

---

## Requeriments

### 1. Àmbit i camp del projecte

Quickserve s'emmarca dins l'àmbit de la digitalització de processos en petits negocis de restauració, concretament en una rostisseria. El focus és la gestió de comandes i encàrrecs, substituint fluxos manuals per fluxos digitals amb millor control operatiu.

### 2. Requeriments funcionals

1. El sistema ha de permetre autenticar usuaris per accedir a les funcionalitats protegides.
2. El sistema ha de mostrar un catàleg de productes i categories actius per a l'operativa del TPV.
3. El sistema ha de permetre registrar vendes amb múltiples línies de producte.
4. El sistema ha de permetre registrar encàrrecs pendents amb hora de recollida i nom de client.
5. El sistema ha de permetre consultar els encàrrecs pendents del dia.
6. El sistema ha de permetre cobrar encàrrecs pendents i actualitzar-ne l'estat.
7. El sistema ha de permetre anul·lar encàrrecs i restaurar l'estoc corresponent.
8. El sistema ha de gestionar automàticament l'estoc dels productes en crear o cancel·lar operacions.
9. El sistema ha de generar identificadors de factura per a comandes cobrades.
10. El sistema ha d'oferir funcionalitats d'administració per gestionar categories, productes, treballadors i comandes.
11. El sistema ha de permetre pujar imatges de producte des del panell d'administració.
12. El sistema ha de proporcionar una API coherent perquè clients externs (p. ex. Flutter) puguin consumir les funcionalitats principals.

### 3. Requeriments no funcionals

1. **Seguretat:** accés a API protegida mitjançant autenticació amb token (Sanctum) i validació de dades d'entrada.
2. **Integritat de dades:** operacions crítiques de comandes dins de transaccions de base de dades per evitar inconsistències.
3. **Rendiment operatiu:** temps de resposta adequat per a ús en entorn de venda en hores punta.
4. **Usabilitat:** interfície orientada a fluxos ràpids i clars per personal no tècnic.
5. **Mantenibilitat:** arquitectura modular (controladors, models, serveis, rutes) i traçabilitat de canvis via GitHub.
6. **Escalabilitat funcional:** disseny preparat per incorporar nous mòduls i clients multiplataforma.
7. **Disponibilitat:** funcionament estable durant l'horari de servei, amb mecanismes de registre d'errors per diagnòstic.

### 4. Diagrames de casos d'ús

En aquesta secció s'han d'incloure els diagrames de casos d'ús amb, com a mínim, els actors següents:

- Personal de venda.
- Administrador.
- Sistema.

Casos d'ús principals recomanats:

- Iniciar sessió.
- Consultar catàleg.
- Crear venda.
- Crear encàrrec.
- Consultar encàrrecs pendents.
- Cobrar encàrrec.
- Cancel·lar encàrrec.
- Gestionar productes/categories/treballadors.

### 5. Fitxes de casos d'ús

S'han de documentar les fitxes textuals dels casos d'ús crítics (precondicions, flux principal, alternatives i postcondicions), especialment:

- UC-01 Crear venda.
- UC-02 Crear encàrrec.
- UC-03 Cobrar encàrrec.
- UC-04 Cancel·lar encàrrec.
- UC-05 Gestionar productes.

### 6. Diagrames d'activitat (funcionalitats principals)

Es recomana incloure com a mínim:

- Flux de creació de venda.
- Flux de creació d'encàrrec.
- Flux de cobrament d'encàrrec pendent.

### 7. Diagrama de classes

El diagrama de classes ha de reflectir, com a mínim, les entitats centrals del domini:

- User
- Worker
- Category
- Product
- Order
- OrderItem

I les relacions principals:

- Order 1..* OrderItem
- Product 1..* OrderItem
- Product *..* Category
- Worker 1..* Order

### 8. Disseny modular del projecte

Quickserve s'ha estructurat en mòduls per responsabilitat:

- **Mòdul d'autenticació:** login, validació de sessió i logout.
- **Mòdul de catàleg:** consulta de categories i productes disponibles.
- **Mòdul de comandes:** alta de vendes i encàrrecs, consulta de pendents, cobrament i cancel·lació.
- **Mòdul d'administració:** gestió de mestres (categories, productes, treballadors) i consulta avançada de comandes.
- **Mòdul de numeració fiscal:** assignació de numeració de factura quan correspon.

### 9. Estructura dels mòduls i dependències

Dependències de més alt nivell:

1. La capa de rutes API delega en controladors especialitzats.
2. Els controladors utilitzen models Eloquent per a persistència.
3. El mòdul de comandes depèn del mòdul de productes per gestionar estoc.
4. El mòdul de comandes depèn del servei de numeració fiscal per completar vendes cobrades.
5. Els clients externs (Flutter) consumeixen el contracte d'API definit.

Aquesta estructura facilita la separació de responsabilitats i simplifica el manteniment del sistema.

---

## Estat de la Secció 2

La secció queda redactada i llesta a nivell de contingut. Com a millora final per a la versió d'entrega, només faltaria afegir els diagrames visuals (UML i Gantt) exportats com a imatges o annexos.
