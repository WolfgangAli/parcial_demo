<html>
  <head>
    <title>Parcial Servidores</title>
  </head> 
  <body>

    <h1 align = 'center'>Parcial Servidores: Listado de Estudiantes</h1>

    <table align = 'center' border = '2'>        
    <?php 
        try {

            $data_source = '';

            $redis = new Redis(); 
            $redis->connect('redis', 6379); 

            $sql = 'select
                    student_id,
                    student_code,
                    first_name,
                    last_name                                 
                    from student
                    ';

            $cache_key = md5($sql);

            if ($redis->exists($cache_key)) {

                $data_source = "Datos desde el Servidor Redis";
                $data = unserialize($redis->get($cache_key));

            } else {

                $data_source = 'Datos desde Base de Datos MySQL';

                $db_name     = 'testdb';
                $db_user     = 'user';
                $db_password = 'test';
                $db_host     = 'db';

                $pdo = new PDO('mysql:host=' . $db_host . '; dbname=' . $db_name, $db_user, $db_password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $data = []; 

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {          
                   $data[] = $row;  
                }  

                $redis->set($cache_key, serialize($data)); 
                $redis->expire($cache_key, 10);        
           }

    </table>
  </body>
</html>
