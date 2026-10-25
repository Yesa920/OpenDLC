#!/bin/bash
# Se ejecuta UNA vez, despues de cargar pccomponentes.sql (el "zz" hace que vaya la ultima).
# El usuario de la web solo puede LEER y ESCRIBIR datos: no puede crear, borrar ni alterar tablas.
# Asi, aunque alguien consiguiera inyectar SQL, no podria destruir la estructura de la base de datos.
set -e
echo ">> Recortando permisos del usuario ${MARIADB_USER} a SELECT, INSERT, UPDATE y DELETE"
mariadb --protocol=socket -uroot -p"${MARIADB_ROOT_PASSWORD}" <<EOSQL
REVOKE ALL PRIVILEGES ON \`${MARIADB_DATABASE}\`.* FROM '${MARIADB_USER}'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON \`${MARIADB_DATABASE}\`.* TO '${MARIADB_USER}'@'%';
FLUSH PRIVILEGES;
EOSQL
