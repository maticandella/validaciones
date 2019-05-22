<?php
//VALIDACION USUARIO, VERIFICA QUE YA EXISTA O NO

include '../conexion/conexion.php';

$nick = $con->real_escape_string($_POST['nick']); //Evita las inyecciones de sql, limpia lo que se escribió en el nick

$sel = $con->query("SELECT id FROM usuario WHERE nick = '$nick'"); // Busca si ya existe el nick que se ha escrito
$row = mysqli_num_rows($sel); // cuenta el nro de filas que encontró
if ($row != 0) {
     echo "<label style='color:red;'>El nombre de usuario ya existe</label>";
} else {
     echo "<label style='color:green;'>El nombre de usuario está disponible</label>";
}
$con->close();
?>







// VALIDACION DE PERMISOS DE USUARIO


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
     // vemos si el metodo es el post
     //evitamos sql inyeccion:
     $user = $con->real_escape_string(htmlentities($_POST['usuario']));
     $pass = $con->real_escape_string(htmlentities($_POST['contra']));
     //usuario y contra vienen del index.php general, del formulario de inicio de sesion

     // validacion
     $candado = ' '; //nos vamos a fijar que no haya espacios
     $str_u = strpos($user,$candado); //strpos() busca caracteres, devuelve un valor integer con la posicion (arranca del 0)
     $str_p = strpos($pass,$candado);
     if (is_int($str_u)) {
          // si encontro un espacio encontró un entero
          $user = '';
     } else {
          $usuario = $user;
     }

     if (is_int($str_p)) {
          // si encontro un espacio encontró un entero
          $pass = '';
     } else {
          // si viene bien la variable se vuelve a encriptar
          $pass2 = sha1($pass);
     }

     if ($user == null && $pass == null) {
          header('location:../extend/alerta.php?msj=El formato no es correcto&c=salir&p=salir&t=error');
     } else {
          // en caso de que todo este bien mandamos a llamar los usuarios que tengamos en la BD
          $sel = $con->query("SELECT nick, nombre, nivel, correo, foto, pass FROM usuario WHERE nick = '$usuario' AND pass = '$pass2' AND bloqueo = 1"); // si el usuario no esta bloqueado tiene el nro 1
          $row = mysqli_num_rows($sel);
          if ($row == 1) {
               // si el nick y el pass son correctos nos tiene que dar si o si 1
               if ($var = $sel->fetch_assoc()) {
                    $nick = $var['nick'];
                    $contra = $var['pass'];
                    $nivel = $var['nivel'];
                    $correo = $var['correo'];
                    $foto = $var['foto'];
                    $nombre = $var['nombre'];
               }

               // validamos para que el usuario pueda entrar

               if ($nick == $usuario && $contra == $pass2 && $nivel == 'ADMINISTRADOR') {
                    // nos fijamos que el usuario sea ADMINISTRADOR, para ver si tiene los permisos necesarios para ingresar
                    $_SESSION['nick'] = $nick;
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['nivel'] = $nivel;
                    $_SESSION['correo'] = $correo;
                    $_SESSION['foto'] = $foto;
                    header('location:../extend/alerta.php?msj=Bienvenido&c=home&p=home&t=success');
               } elseif ($nick == $usuario && $contra == $pass2 && $nivel == 'ASESOR') {
                    // nos fijamos que el usuario sea ASESOR
                    $_SESSION['nick'] = $nick;
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['nivel'] = $nivel;
                    $_SESSION['correo'] = $correo;
                    $_SESSION['foto'] = $foto;
                    header('location:../extend/alerta.php?msj=Bienvenido&c=home&p=home&t=success');
               } else {
                    header('location:../extend/alerta.php?msj=No tienes el permiso para entrar&c=salir&p=salir&t=error');
               }
          } else {
               // si el usuario se equivoco en la contra por ejemplo
               header('location:../extend/alerta.php?msj=Nombre de usuario o contraseña incorrectos&c=salir&p=salir&t=error');
          }
     }

} else {
     header('location:../extend/alerta.php?msj=Utiliza el formulario&c=salir&p=salir&t=error');
}






//  DAR PERMISOS

<?php
if($_SESSION['nivel'] != 'ADMINISTRADOR' ) {
     //si algun asesor intenta ingresar por medio del link a la carpeta de usuarios, lo vamos a bloquear
     header("location:bloqueo.php");
}

?>

Y EN EL ARCHIVO HEADER:
<?php
       if ($_SESSION['nivel'] == 'ADMINISTRADOR') {
            include 'menu_admin.php';
       } else {
            include 'menu_asesor.php';
       }
     
     ?>






// VALIDACIONES GENERALES
<?php
include '../conexion/conexion.php';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
     // Si los datos se envian por el metodo POST se realiza lo siguiente:
     // trae los datos, limpia y valida
     $nick = $con->real_escape_string(htmlentities($_POST['nick'])); 
     $pass1 = $con->real_escape_string(htmlentities($_POST['pass1']));
     $nivel = $con->real_escape_string(htmlentities($_POST['nivel']));
     $nombre = $con->real_escape_string(htmlentities($_POST['nombre']));
     $correo = $con->real_escape_string(htmlentities($_POST['correo']));

     //validaciones

     //que no haya campos vacios:
     if (empty($nick) || empty($pass1) || empty($nivel) || empty($nombre)) {
          header('location:../extend/alerta.php?msj=Hay un campo sin especificar&c=us&p=in&t=error');
          exit;
     }

     //comprobar que contiene solo letras:
     if (!ctype_alpha($nick)) {
          header('location:../extend/alerta.php?msj=El nick no contiene solo letras&c=us&p=in&t=error');
          exit;
     }
     if (!ctype_alpha($nivel)) {
          header('location:../extend/alerta.php?msj=El nivel no contiene solo letras&c=us&p=in&t=error');
          exit;
     }

     // comprobar el nombre, puede contener espacios, porque puede ser una o mas palabras
     // primero vemos cuales son los caracteres permitidos
     $caracteres = "ABCDEFGHIJKLMNÑOPQRSTUVWXYZ ";
     for ($i=0; $i < strlen($nombre); $i++) {
          //buscamos de a una letra de cada nombre, por ejemplo si es ALEJANDRO empezamos con la A
          $buscar = substr($nombre,$i,1);
          if (strpos($caracteres,$buscar) === false) {
               //comprueba si se encontro el caracter dentro de los permitidos
                    header('location:../extend/alerta.php?msj=El nombre no contiene solo letras&c=us&p=in&t=error');
                    exit;
          }
     }

     //validacion del nick
     $usuario = strlen($nick);
     $contra = strlen($pass1);

     if ($usuario < 8 || $usuario > 15) {
          header('location:../extend/alerta.php?msj=El nick debe contener entre 8 y 15 caracteres&c=us&p=in&t=error');
                    exit;
     }
     if ($contra < 8 || $contra > 15) {
          header('location:../extend/alerta.php?msj=La contraseña debe contener entre 8 y 15 caracteres&c=us&p=in&t=error');
                    exit;
     }

     // validacion del correo
     if (!empty($correo)) {
          // si el correo se escribió, entonces se valida:
          if(!filter_var($correo,FILTER_VALIDATE_EMAIL)) {
               header('location:../extend/alerta.php?msj=El email no es válido&c=us&p=in&t=error');
                    exit;
          }
     }

     // foto de perfil
     $extension ='';
     $ruta = 'foto_perfil';
     $archivo = $_FILES['foto']['tmp_name'];
     $nombrearchivo = $_FILES['foto']['name'];
     $info = pathinfo($nombrearchivo);
     if($archivo != '') {
          // vamos a usar la extension para validar que no se ingresen extensiones tipo pdf, texto, etc
          $extension = $info['extension'];
          if ($extension == "png" || $extension == "PNG" || $extension == "jpg" || $extension == "JPG" || $extension == "jpeg" || $extension == "JPEG") {
               move_uploaded_file($archivo,'foto_perfil/'.$nick.'.'.$extension);
               $ruta = $ruta."/".$nick.'.'.$extension;
          } else {
               header('location:../extend/alerta.php?msj=El formato no es valido&c=us&p=in&t=error');
                    exit;
          }
     } else {
          $ruta = "foto_perfil/perfil.png";
     }

     // ahora si encriptamos la contraseña
     $pass1 = sha1($pass1);
     // he insertamos los datos del nuevo usuario en la BD
     $ins = $con->query("INSERT INTO usuario VALUES('','$nick','$pass1','$nombre','$correo','$nivel',1,'$ruta') ");
     if ($ins) {
          header('location:../extend/alerta.php?msj=El usuario ha sido registrado&c=us&p=in&t=success');
     } else {
          header('location:../extend/alerta.php?msj=El usuario no pudo ser registrado&c=us&p=in&t=error');
     }
     $con->close();
     
} else {
     // Si quieren ingresar directamente a ins_usuarios.php va a saltar la siguiente alerta:
     header('location:../extend/alerta.php?msj=Utiliza el formulario&c=us&p=in&t=error');
     //envia el mensaje al archivo de alertas para diseñar la alerta, "c" es por la carpeta, "p" por la pagina index, y "t" por tipo error
}

?>






// VALIDACION USUARIO BLOQUEADO

if ($bloqueo == 1) {
     // si esta activo lo bloqueamos
     $up = $con->query("UPDATE usuario SET bloqueo=0 WHERE id='$id' ");
     if ($up) {
          // si esto funciona enviamos un alerta
          header('location:../extend/alerta.php?msj=El usuario ha sido bloqueado&c=us&p=in&t=success');
     } else {
          header('location:../extend/alerta.php?msj=El usuario no ha podido ser bloqueado&c=us&p=in&t=error');
     }
} else {
     // si esta bloqueado lo desbloqueamos
     $up = $con->query("UPDATE usuario SET bloqueo=1 WHERE id='$id' ");
     if ($up) {
          // si esto funciona enviamos un alerta
          header('location:../extend/alerta.php?msj=El usuario ha sido desbloqueado&c=us&p=in&t=success');
     } else {
          header('location:../extend/alerta.php?msj=El usuario no ha podido ser desbloqueado&c=us&p=in&t=error');
     }
}
