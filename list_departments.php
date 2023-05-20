<!DOCTYPE html>
<html>
<head>
    <title>List Departments</title>
</head>
<body>
    <h1>Departments</h1>
    <style>
        table {
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
            padding: 5px;
        }
    </style>
    <?php
    require 'vendor/autoload.php'; 
    use Dotenv\Dotenv;
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // DATABASE
    $servername = $_ENV['DB_HOST'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];
    $dbname = $_ENV['DB_DATABASE'];

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        exit('Database connection error.');
    }

    // SQL QUERY TO GET DEPARTMENTS
    $sql = "SELECT 
                d.dept_no AS `Department Number`,
                d.dept_name AS `Department Name`,
                CONCAT(m.first_name, ' ', m.last_name) AS `Manager Name`,
                dm.from_date AS `From Date`,
                dm.to_date AS `To Date`,
                DATEDIFF(dm.to_date, dm.from_date) AS `Number of Years`,
                CONCAT('<a href=\"list_employees.php?department=', d.dept_no, '&manager=', m.emp_no, '\">View Employees</a>') AS `Link`
            FROM
                departments d
                INNER JOIN dept_manager dm ON dm.dept_no = d.dept_no
                INNER JOIN employees m ON m.emp_no = dm.emp_no;";

    $statement = $conn->prepare($sql);
    $statement->execute();
    $departments = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($departments)) {
        echo '<table>
                <thead>
                    <tr>
                        <th>Department Number</th>
                        <th>Department Name</th>
                        <th>Manager Name</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Number of Years</th>
                        <th>Link</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($departments as $department) {
            echo '<tr>';
            echo '<td>' . $department['Department Number'] . '</td>';
            echo '<td>' . $department['Department Name'] . '</td>';
            echo '<td>' . $department['Manager Name'] . '</td>';
            echo '<td>' . $department['From Date'] . '</td>';
            echo '<td>' . $department['To Date'] . '</td>';
            echo '<td>' . $department['Number of Years'] . '</td>';
            echo '<td>' . $department['Link'] . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    } else {
        echo 'No departments found.';
    }
    ?>

</body>
</html>
