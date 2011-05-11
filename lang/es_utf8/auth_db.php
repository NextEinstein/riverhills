<?php

// All of the language strings in this file should also exist in
// auth.php to ensure compatibility in all versions of Moodle.

$string['auth_dbcantconnect'] = 'No se ha podido conectar con la base de datos de autenticación especificada...';
$string['auth_dbchangepasswordurl_key'] = 'URL de cambio de contraseña';
$string['auth_dbdebugauthdb'] = 'Depurar ADPdb';
$string['auth_dbdebugauthdbhelp'] = 'Depurar conexión ADOdb a una base de datos externa - Utilizarlo cuando se esté obteniendo una página en blanco durante el inicio de sesión. No es conveniente para sitios de producción.';
$string['auth_dbdeleteuser'] = 'Eliminado el usuario $a[0] id $a[1]';
$string['auth_dbdeleteusererror'] = 'Error  al eliminar al usuario $a';
$string['auth_dbdescription'] = 'Este método utiliza una tabla de una base de datos externa para comprobar si un determinado usuario y contraseña son válidos. Si la cuenta es nueva, la información de otros campos puede también ser copiada en Moodle.';
$string['auth_dbextencoding'] = 'Codificación de base de datos externa';
$string['auth_dbextencodinghelp'] = 'Codificación del usuario en base de datos externa';
$string['auth_dbextrafields'] = 'Estos campos son opcionales. Usted puede elegir pre-rellenar algunos campos del usuario de Moodle con información desde los <strong>campos de la base de datos externa</strong> que especifique aquí. <p>Si deja esto en blanco, se tomarán los valores por defecto</p>.<p>En ambos casos, el usuario podrá editar todos estos campos después de entrar</p>.';
$string['auth_dbfieldpass'] = 'Nombre del campo que contiene las contraseñas';
$string['auth_dbfieldpass_key'] = 'Campo de contraseña';
$string['auth_dbfielduser'] = 'Nombre del campo que contiene los nombres de usuario';
$string['auth_dbfielduser_key'] = 'Campo de nombre de usuario';
$string['auth_dbhost'] = 'El ordenador que hospeda el servidor de la base de datos.';
$string['auth_dbhost_key'] = 'Host';
$string['auth_dbinsertuser'] = 'Insertado el usuario $a[0] id $a[1]';
$string['auth_dbinsertusererror'] = 'Error al insertar al usuario $a';
$string['auth_dbname'] = 'Nombre de la base de datos';
$string['auth_dbname_key'] = 'Nombre de la BD';
$string['auth_dbpass'] = 'Contraseña correspondiente al nombre de usuario anterior';
$string['auth_dbpass_key'] = 'Contraseña';
$string['auth_dbpasstype'] = 'Especifique el formato que usa el campo de contraseña. La encriptación MD5 es útil para conectar con otras aplicaciones web como PostNuke';
$string['auth_dbpasstype_key'] = 'Formato de contraseña';
$string['auth_dbreviveduser'] = 'Recuperado el usuario $a[0] id $a[1]';
$string['auth_dbrevivedusererror'] = 'Error al recuperar al usuario $a';
$string['auth_dbsetupsql'] = 'Comando de ajuste SQL';
$string['auth_dbsetupsqlhelp'] = 'Comando SQL para la configuración especial de la base de datos, comúnmente se utiliza para la codificación de comunicación - ejemplo para MySQL y PostgreSQL: <em>SET NAMES \'utf8\'</em>';
$string['auth_dbsuspenduser'] = 'Suspendido el usuario $a[0] id $a[1]';
$string['auth_dbsuspendusererror'] = 'Error al suspender al usuario $a';
$string['auth_dbsybasequoting'] = 'Utilizar citaciones (quotes) de sybase';
$string['auth_dbsybasequotinghelp'] = 'Escapado de comilla simple al estilo Sybase - necesario para Oracle, MS SQL y algunas otras bases de datos. ¡No lo utilice para MySQL!';
$string['auth_dbtable'] = 'Nombre de la tabla en la base de datos';
$string['auth_dbtable_key'] = 'Tabla';
$string['auth_dbtitle'] = 'Usar una base de datos externa';
$string['auth_dbtype'] = 'El tipo de base de datos (Vea la <a href=../lib/adodb/readme.htm#drivers>documentación de ADOdb</a> para obtener más detalles)';
$string['auth_dbtype_key'] = 'Base de datos';
$string['auth_dbupdatinguser'] = 'Actualizando al usuario $a[0] id $a[1]';
$string['auth_dbuser'] = 'Nombre de usuario con acceso de lectura a la base de datos';
$string['auth_dbuser_key'] = 'Usuario de BD';
$string['auth_dbusernotexist'] = 'No se puede actualizar un usuario no existente: $a';
$string['auth_dbuserstoadd'] = 'Entradas de usuario a añadir: $a';
$string['auth_dbuserstoremove'] = 'Entradas de usuario a eliminar: $a';