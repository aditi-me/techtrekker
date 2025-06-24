<?php
include 'header.php';
require 'db.php'; // This line establishes the $connection

$sql = "
    SELECT
        course_id,
        course_name,
        estimated_duration,
        price,
        instructor_name,
        skill_level,
        average_rating,
        num_reviews,
        short_tagline,
        num_students_enrolled,
        display_stream_name
    FROM
        courses
    ORDER BY
        display_stream_name, course_name;
";

$result = $connection->query($sql);

?>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .card {
            background-color: #fff; /* Default, will be overridden by media queries for glass effect */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            padding: 20px;
            max-width: 1400px;
        }

        .card-header {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            background-color: #a78bfa; /* Matches your header color */
            color: white;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px -20px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd; /* Default border color */
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2; /* Default header background */
            color: #555; /* Default header text color */
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        /* Remove specific row coloring classes for a unified look */
        /* .row-color-0 { background-color: #FFCDD2 !important; } */
        /* .row-color-1 { background-color: #FFF9C4 !important; } */
        /* ... and so on for other row-color-X classes ... */

        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: flex-end;
        }

        .edit-btn{
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
        }

        .edit-btn {
            background-color: #007bff;
            color: white;
        }

        .edit-btn:hover {
            background-color: #0056b3;
        }

        

        

        .fas {
            margin-right: 5px;
        }

        /* --- Glass Effect and Responsiveness for all screen sizes --- */
        /* Apply glass effect for screens wider than 768px (tablets and desktops) */
        @media (min-width: 768px) {
            .card {
                background-color: rgba(167, 139, 250, 0.81); /* Semi-transparent background */
                backdrop-filter: blur(15px); /* Glass effect blur */
                -webkit-backdrop-filter: blur(15px); /* Safari compatibility */
                color: #fff; /* White text for glass effect */
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
                width: 90vw; /* Responsive width */
                max-width: 1400px; /* Max width to prevent it from getting too wide */
            }

            .card-header {
                color: #fff; /* Ensure header text is white */
                border-bottom-color: rgba(255, 255, 255, 0.5); /* Lighter border for glass effect */
            }

            th {
                background-color: rgba(255, 255, 255, 0.2); /* Lighter input background for glass effect */
                color: #fff; /* White text in headers */
                border-color: rgba(255, 255, 255, 0.5); /* Lighter border for glass effect */
            }

            td {
                color: #fff; /* White text in table data */
                border-color: rgba(255, 255, 255, 0.5); /* Lighter border for glass effect */
            }
            
            /* Apply a single background for all rows under glass effect */
            table tr {
                background-color: rgba(255, 255, 255, 0.1); /* Consistent transparent white for all rows */
            }

            /* Remove nth-child(even) styling to keep one color */
            table tr:nth-child(even) {
                background-color: rgba(255, 255, 255, 0.1); /* Ensure it's the same */
            }

            table tr:hover {
                background-color: rgba(255, 255, 255, 0.3); /* Hover effect for rows */
            }
        }

        /* --- Glass Effect for Smaller Screens (Phones and Tablets) --- */
        /* Apply glass effect for screens smaller than 768px */
        @media (max-width: 767.98px) {
            .card {
                background-color: rgba(167, 139, 250, 0.81); /* Semi-transparent background */
                backdrop-filter: blur(10px); /* Slightly less blur for smaller screens */
                -webkit-backdrop-filter: blur(10px); /* Safari compatibility */
                color: #fff;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
                width: 95vw; /* Take up more width on smaller screens */
                padding: 20px; /* Slightly less padding */
            }

            .card-header {
                color: #fff;
                border-bottom-color: rgba(255, 255, 255, 0.5);
            }

            th {
                background-color: rgba(255, 255, 255, 0.2);
                color: #fff;
                border-color: rgba(255, 255, 255, 0.5);
            }

            td {
                color: #fff;
                border-color: rgba(255, 255, 255, 0.5);
            }

            /* Apply a single background for all rows under glass effect */
            table tr {
                background-color: rgba(255, 255, 255, 0.1); /* Consistent transparent white for all rows */
            }
            
            /* Remove nth-child(even) styling to keep one color */
            table tr:nth-child(even) {
                background-color: rgba(255, 255, 255, 0.1); /* Ensure it's the same */
            }

            table tr:hover {
                background-color: rgba(255, 255, 255, 0.3);
            }
        }
    </style>

<body>

    <div class="card">
        <div class="card-header">
            Teacher Panel: Course List
        </div>
        <table>
            <thead>
                <tr>
                    <th>Stream</th>
                    <th>Course Name</th>
                    <th>Instructor</th>
                    <th>Skill Level</th>
                    <th>Estimated Duration</th>
                    <th>Price</th>
                    <th>Avg. Rating</th>
                    <th>No. Reviews</th>
                    <th>Enrolled Students</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Removed $row_counter and $row_class for single color
                        echo "<tr>"; // No class attribute for unified color
                        echo "<td>" . htmlspecialchars($row['display_stream_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['instructor_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['skill_level']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['estimated_duration']) . "</td>";
                        echo "<td>₹" . number_format($row['price'], 2) . "</td>"; // Format as Indian Rupees
                        echo "<td>" . htmlspecialchars($row['average_rating']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['num_reviews']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['num_students_enrolled']) . "</td>";
                        

                        
                        
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No courses found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

<?php include 'footer.php'; ?>

<?php
// Close the connection AFTER all includes and HTML rendering
$connection->close();
?>
</body>
