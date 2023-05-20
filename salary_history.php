<!DOCTYPE html>
<html>
<head>
    <title>Salary History</title>
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
    <?php
    $employeeId = $_GET['employee_id'];

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

    // SQL QUERY FOR GETTING EMPLOYEE INFO
    $sql_employee = "SELECT
                        CONCAT(first_name, ' ', last_name) AS 'Name',
                        birth_date AS 'Birthday',
                        TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) AS 'Age',
                        gender AS 'Gender',
                        hire_date AS 'Hire Date'
                    FROM
                        employees
                    WHERE
                        emp_no = :employee_id";

    $statement_employee = $conn->prepare($sql_employee);
    $statement_employee->bindParam(':employee_id', $employeeId);
    $statement_employee->execute();
    $employee = $statement_employee->fetch(PDO::FETCH_ASSOC);

    // CHECK IF EMPLOYEE EXISTS
    if (empty($employee)) {
        // CANNOT FIND EMPLOYEE
        echo "<h2>Invalid Employee Number: $employeeId</h2>";
    } else {
        // DISPLAY EMPLOYEE INFO AS HEADING
        echo "<h2>Employee Information for Employee ID: $employeeId</h2>";
        echo "<h3>Name: {$employee['Name']}</h3>";
        echo "<p>Birthday: {$employee['Birthday']}</p>";
        echo "<p>Age: {$employee['Age']}</p>";
        echo "<p>Gender: {$employee['Gender']}</p>";
        echo "<p>Hire Date: {$employee['Hire Date']}</p>";

        // SQL QUERY FOR GETTING EMPLOYEE SALARY HISTORY
        $sql_salary = "SELECT
                            from_date AS 'From Date',
                            to_date AS 'To Date',
                            salary AS 'Salary'
                        FROM
                            salaries
                        WHERE
                            emp_no = :employee_id";

        $statement_salary = $conn->prepare($sql_salary);
        $statement_salary->bindParam(':employee_id', $employeeId);
        $statement_salary->execute();
        $salary_records = $statement_salary->fetchAll(PDO::FETCH_ASSOC);

        // CHECK IF THERE'S RECORDS
        if (empty($salary_records)) {
            // NO RECORDS FOUND
            echo "<h3>No Salary History Found</h3>";
        } else {
            // DISPLAY SALARY HISTORY ON TABLE
            echo "<h3>Salary History</h3>";
            echo "<table>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>From Date</th>";
            echo "<th>To Date</th>";
            echo "<th>Salary</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            foreach ($salary_records as $record) {
                echo "<tr>";
                echo "<td>{$record['From Date']}</td>";
                echo "<td>{$record['To Date']}</td>";
                echo "<td>{$record['Salary']}</td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
        }
    }
    ?>
</body>
</html>
