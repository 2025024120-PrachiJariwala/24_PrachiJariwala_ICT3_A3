<?php
$url = "http://localhost:3001/api/students";

// Set HTTP request options
$options = [
    "http" => [
        "method" => "GET",
        "header" => "Accept: application/json\r\n",
        "timeout" => 5
    ]
];

// Create request context
$context = stream_context_create($options);

// Call Express API
$response = @file_get_contents($url, false, $context);
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP Calling Express API</title>
</head>
<body>

    <h2>Student List from Express REST API</h2>

    <?php
    if ($response === false) {
        echo "<p>Unable to connect to Express API. Please start the Node.js server.</p>";
    } else {
        $result = json_decode($response, true);

        if (
            !is_array($result) ||
            !isset($result["success"]) ||
            $result["success"] !== true ||
            !isset($result["data"]) ||
            !is_array($result["data"])
        ) {
            echo "<p>Invalid API response.</p>";
        } else {
    ?>

        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>City</th>
            </tr>

            <?php foreach ($result["data"] as $student) { ?>
                <tr>
                    <td>
                        <?= htmlspecialchars((string)$student["id"]) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($student["name"]) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($student["city"]) ?>
                    </td>
                </tr>
            <?php } ?>

        </table>

    <?php
        }
    }
    ?>

</body>
</html>