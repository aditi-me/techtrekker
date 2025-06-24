<?php
// Ensure db.php is included to establish the database connection
require 'db.php';

// -----------------------------------------------------------
// Fetch All Holidays for Display
// -----------------------------------------------------------
$holidays = [];
$fetch_error = ''; // Initialize error variable

// Prepare the SQL query to select all holidays, ordered by date
$sql = "SELECT id, name, holiday_date FROM holidays ORDER BY holiday_date ASC";

// Attempt to execute the query
if ($result = $connection->query($sql)) {
    // Check if any rows were returned
    if ($result->num_rows > 0) {
        // Loop through each row and add it to the holidays array
        while ($row = $result->fetch_assoc()) {
            $holidays[] = $row;
        }
    }
    // Free the result set
    $result->free();
} else {
    // If there's an error in the query, store the error message
    $fetch_error = "ERROR: Could not fetch holidays. " . $connection->error;
}

// -----------------------------------------------------------
// Include the header file (assuming it contains necessary HTML head, body opening tags, and styling)
// -----------------------------------------------------------
require_once 'header.php';
?>

<div class="holiday-wrapper" style="margin-top: 50px;">
    <div class="container">
        <div class="card p-4">
            <h1 class="text-center mb-4">Yearly Holidays!</h1>

            <?php
            // Display any fetch errors if they occurred
            if (!empty($fetch_error)) {
                echo "<p class='text-danger text-center'>" . $fetch_error . "</p>";
            }
            ?>

            <div class="p-4 border rounded card">
                <h2 class="mb-3">Existing Holidays</h2>
                <?php if (!empty($holidays)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($holidays as $holiday): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($holiday['name']); ?></td>
                                <td><?php echo htmlspecialchars($holiday['holiday_date']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-center text-muted">No holidays found in the database.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// -----------------------------------------------------------
// Include the footer file (assuming it contains necessary closing body and html tags)
// -----------------------------------------------------------
require_once 'footer.php';

// -----------------------------------------------------------
// Close Database Connection
// -----------------------------------------------------------
$connection->close();
?>
