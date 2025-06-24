<?php
require 'db.php';


// -----------------------------------------------------------
// 3. Handle Add Holiday Request
// -----------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_holiday') {
    $holiday_name = trim($_POST['holiday_name']);
    $holiday_date = trim($_POST['holiday_date']);

    // Basic validation
    if (empty($holiday_name) || empty($holiday_date)) {
        $add_message = "<p class='text-danger'>Please fill in all fields to add a holiday.</p>";
    } else {
        // Prepare an insert statement to prevent SQL injection
        $sql = "INSERT INTO holidays (name, holiday_date) VALUES (?, ?)";

        if ($stmt = $connection->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ss", $param_name, $param_date);

            // Set parameters
            $param_name = $holiday_name;
            $param_date = $holiday_date;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $add_message = "<p class='text-success'>Holiday added successfully!</p>";
            } else {
                // Check for duplicate entry error (e.g., if holiday_date is UNIQUE)
                if ($connection->errno == 1062) { // MySQL error code for duplicate entry
                    $add_message = "<p class='text-danger'>Error: A holiday already exists on this date.</p>";
                } else {
                    $add_message = "<p class='text-danger'>Error adding holiday: " . $stmt->error . "</p>";
                }
            }
            // Close statement
            $stmt->close();
        } else {
            $add_message = "<p class='text-danger'>ERROR: Could not prepare statement to add holiday.</p>";
        }
    }
}

// -----------------------------------------------------------
// 4. Handle Delete Holiday Request
// -----------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_holiday') {
    $holiday_id = trim($_POST['id']);

    if (!empty($holiday_id) && is_numeric($holiday_id)) {
        // Prepare a delete statement to prevent SQL injection
        $sql = "DELETE FROM holidays WHERE id = ?";

        if ($stmt = $connection->prepare($sql)) {
            $stmt->bind_param("i", $param_id);
            $param_id = $holiday_id;

            if ($stmt->execute()) {
                $delete_message = "<p class='text-success'>Holiday deleted successfully!</p>";
            } else {
                $delete_message = "<p class='text-danger'>Error deleting holiday: " . $stmt->error . "</p>";
            }
            $stmt->close();
        } else {
            $delete_message = "<p class='text-danger'>ERROR: Could not prepare statement to delete holiday.</p>";
        }
    } else {
        $delete_message = "<p class='text-danger'>Invalid holiday ID for deletion.</p>";
    }
}

// -----------------------------------------------------------
// 5. Fetch All Holidays for Display
// -----------------------------------------------------------
$holidays = [];
$sql = "SELECT id, name, holiday_date FROM holidays ORDER BY holiday_date ASC";
if ($result = $connection->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $holidays[] = $row;
        }
    }
    $result->free();
} else {
    $fetch_error = "ERROR: Could not fetch holidays. " . $connection->error;
}

// -----------------------------------------------------------
// 6. Close Database Connection
// -----------------------------------------------------------


// Include the header file
require_once 'header.php';
?>

<div class="holiday-wrapper" style="margin-top: 50px;">
    <div class="container">
        <div class="card p-4">
            <h1 class="text-center mb-4">Holiday Management Panel!</h1>

            <?php
            // Display messages
            if (isset($add_message)) { echo $add_message; }
            if (isset($delete_message)) { echo $delete_message; }
            if (isset($fetch_error)) { echo "<p class='text-danger'>" . $fetch_error . "</p>"; }
            ?>

            <div class="mb-4 p-4 border rounded card">
                <h2 class="mb-3">Add New Holiday</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="add_holiday">
                    <div class="mb-3">
                        <label for="holiday_name" class="form-label">Holiday Name:</label>
                        <input type="text" id="holiday_name" name="holiday_name" placeholder="e.g., Christmas" required
                               class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="holiday_date" class="form-label">Holiday Date:</label>
                        <input type="date" id="holiday_date" name="holiday_date" required
                               class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        Add Holiday
                    </button>
                </form>
            </div>

            <div class="p-4 border rounded card">
                <h2 class="mb-3">Existing Holidays</h2>
                <?php if (!empty($holidays)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Date</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($holidays as $holiday): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($holiday['name']); ?></td>
                                <td><?php echo htmlspecialchars($holiday['holiday_date']); ?></td>
                                <td>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="d-inline-block">
                                        <input type="hidden" name="action" value="delete_holiday">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($holiday['id']); ?>">
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this holiday?');">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-center text-muted">No holidays found. Add a new holiday above!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer file
require_once 'footer.php';
$connection->close();
?>