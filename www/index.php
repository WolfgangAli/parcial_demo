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
          
           echo "<tr><td colspan = '4' align = 'center'><h2>$data_source</h2></td></tr>";
           echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Apellido</th></tr>";

           foreach ($data as $record) {
              echo '<tr>';
              echo '<td>' . $record['student_id'] . '</td>';
              echo '<td>' . $record['student_code'] . '</td>';
              echo '<td>' . $record['first_name'] . '</td>';
              echo '<td>' . $record['last_name']  . '</td>';                     
              echo '</tr>'; 
           }              


        } catch (PDOException $e) {
            echo 'Error en BD. ' . $e->getMessage();
        }
   ?>

    </table>
  </body>
</html>
