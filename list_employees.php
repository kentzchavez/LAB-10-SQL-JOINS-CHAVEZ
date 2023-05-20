<!DOCTYPE html>
<html>
<head>
    <title>Employees</title>
    <style>
        table {
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
            padding: 5px;
        }
    </style>
</head>
<body>
    <h1>List of Employees</h1>
    <?php
    // GET DEPARTMENT NUMBER AND MANAGER NUMBER
    $departmentNumber = $_GET['department'];
    $managerEmployeeNumber = $_GET['manager'];

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

    // GET AND DISPLAY DEPARTMENT NAME AND MANAGER FOR THE HEADING

    //SQL QUERY
    $departmentNameSql = "SELECT dept_name, CONCAT(m.first_name, ' ', m.last_name) AS manager_name
                            FROM departments d
                            INNER JOIN dept_manager dm ON dm.dept_no = d.dept_no
                            INNER JOIN employees m ON m.emp_no = dm.emp_no
                            WHERE d.dept_no = :dept_no
                            AND m.emp_no = :manager_emp_no";
    $departmentNameStatement = $conn->prepare($departmentNameSql);
    $departmentNameStatement->bindParam(':dept_no', $departmentNumber);
    $departmentNameStatement->bindParam(':manager_emp_no', $managerEmployeeNumber);
    $departmentNameStatement->execute();
    $departmentNameResult = $departmentNameStatement->fetch(PDO::FETCH_ASSOC);

    if (!$departmentNameResult) {
        exit('Department or manager not found.');
    }

    $departmentName = $departmentNameResult['dept_name'];
    $managerName = $departmentNameResult['manager_name'];
    ?>
    <h2>Department: <?php echo $departmentName; ?></h2>
    <h2>Manager: <?php echo $managerName; ?></h2>

    <?php
    // SQL QUERY TO GET EMPLOYEE INFO
    $sql = "SELECT
                t.title AS `Title`,
                CONCAT(e.first_name, ' ', e.last_name) AS `Name`,
                e.birth_date AS `Birthday`,
                TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) AS `Age`,
                e.gender AS `Gender`,
                e.hire_date AS `Hire Date`,
                s.salary AS `Latest Salary`,
                CONCAT('<a href=\"salary_history.php?employee_id=', e.emp_no, '\">View Salary History</a>') AS `Link`
            FROM
                departments d
                INNER JOIN dept_manager dm ON dm.dept_no = d.dept_no
                INNER JOIN employees m ON m.emp_no = dm.emp_no
                INNER JOIN dept_emp de ON de.dept_no = d.dept_no
                INNER JOIN employees e ON e.emp_no = de.emp_no
                INNER JOIN titles t ON t.emp_no = e.emp_no
                INNER JOIN salaries s ON s.emp_no = e.emp_no
                    AND s.to_date = (
                        SELECT MAX(to_date)
                        FROM salaries
                        WHERE emp_no = e.emp_no
                    )
            WHERE
                d.dept_no = :dept_no
                AND m.emp_no = :manager_emp_no";

    $statement = $conn->prepare($sql);
    $statement->bindParam(':dept_no', $departmentNumber);
    $statement->bindParam(':manager_emp_no', $managerEmployeeNumber);
    $statement->execute();
    $records = $statement->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Name</th>
                <th>Birthday</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Hire Date</th>
                <th>Latest Salary</th>
                <th>Link</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><?php echo $record['Title']; ?></td>
                    <td><?php echo $record['Name']; ?></td>
                    <td><?php echo $record['Birthday']; ?></td>
                    <td><?php echo $record['Age']; ?></td>
                    <td><?php echo $record['Gender']; ?></td>
                    <td><?php echo $record['Hire Date']; ?></td>
                    <td><?php echo $record['Latest Salary']; ?></td>
                    <td><?php echo $record['Link']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
