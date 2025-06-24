-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jun 24, 2025 at 06:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `techtrekker`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `stream_id` int(11) DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `course_name` varchar(255) DEFAULT NULL,
  `estimated_duration` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `key_topics_summary` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `instructor_name` varchar(255) DEFAULT NULL,
  `skill_level` varchar(50) DEFAULT NULL,
  `average_rating` float DEFAULT NULL,
  `num_reviews` int(11) DEFAULT NULL,
  `short_tagline` text DEFAULT NULL,
  `num_students_enrolled` int(11) DEFAULT NULL,
  `prerequisites` text DEFAULT NULL,
  `badge_label` varchar(50) DEFAULT NULL,
  `display_stream_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`stream_id`, `course_id`, `course_name`, `estimated_duration`, `price`, `key_topics_summary`, `image_url`, `instructor_name`, `skill_level`, `average_rating`, `num_reviews`, `short_tagline`, `num_students_enrolled`, `prerequisites`, `badge_label`, `display_stream_name`) VALUES
(1, 101, 'Data Structures & Algorithms in Python', 'Approx. 4 Months', 7500.00, 'Trees, Graphs, Sorting, Searching, Dynamic Programming.', 'https://www.digitalvidya.com/blog/wp-content/uploads/2018/11/DATA-STRUCTURES-AND-ALGORITHMS-IN-P-952x500.png', 'Dr. Alok Sharma', 'Intermediate', 4.8, 1250, 'Master core DSA concepts with Python.', 15000, 'Basic Python, College Math', 'Popular', 'Computer Science and Engineering'),
(1, 102, 'Operating Systems Fundamentals', 'Designed for 35 Classes', 6000.00, 'Processes, Memory Management, File Systems, Concurrency.', 'https://www.ionos.com/digitalguide/fileadmin/DigitalGuide/Teaser/operating-system-t.jpg', 'Prof. Priya Singh', 'Intermediate', 4.6, 980, 'Understand the backbone of modern computing.', 10200, 'Basic C/C++', 'Live Class', 'Computer Science and Engineering'),
(1, 103, 'Database Management Systems (DBMS)', 'Approx. 3 Months', 5500.00, 'SQL, ER Models, Normalization, Transactions, Concurrency Control.', 'https://files.codingninjas.in/article_images/custom-upload-1682777814.webp', 'Dr. Alok Sharma', 'Beginner', 4.7, 1120, 'Learn to design and manage robust databases.', 13000, 'None', 'New', 'Computer Science and Engineering'),
(1, 104, 'Computer Networks Basics', 'Designed for 30 Classes', 5000.00, 'OSI/TCP-IP Models, Routing, Switching, Network Security.', 'https://miro.medium.com/v2/resize:fit:1024/0*yDZ4O2EsLoVJSdDC.jpeg', 'Prof. Priya Singh', 'Beginner', 4.5, 870, 'Connect the dots: from local to global networks.', 9500, 'Basic Internet knowledge', NULL, 'Computer Science and Engineering'),
(1, 105, 'Software Engineering Principles', 'Approx. 2.5 Months', 4800.00, 'SDLC, Agile Methodologies, Requirements Gathering, Testing.', 'https://images.clickittech.com/2020/wp-content/uploads/2023/04/27230134/Banner-83-1.jpg', 'Dr. Rohan Kumar', 'All Levels', 4.4, 750, 'Build scalable and maintainable software systems.', 8800, 'None', NULL, 'Computer Science and Engineering'),
(2, 201, 'Machine Learning with Python & Scikit-learn', 'Approx. 3 Months', 8500.00, 'Supervised/Unsupervised Learning, Regression, Classification, Clustering.', 'https://i.ytimg.com/vi/XdJAF_InNGA/maxresdefault.jpg', 'Dr. Sanjay Gupta', 'Beginner', 4.9, 1800, 'Kickstart your AI career.', 20000, 'Basic Python, Linear Algebra', 'Popular', 'CSE AIML'),
(2, 202, 'Deep Learning with TensorFlow & Keras', 'Designed for 45 Classes', 12000.00, 'Neural Networks, CNNs, RNNs, LSTMs, Transformers.', 'https://www.educba.com/academy/wp-content/uploads/2020/02/Deep-Learning-with-TensorFlow.jpg', 'Prof. Neha Sharma', 'Intermediate', 4.8, 1500, 'Build powerful AI models.', 17000, 'ML basics, Python', 'Live Class', 'CSE AIML'),
(2, 203, 'Natural Language Processing (NLP) Masterclass', 'Approx. 4 Months', 10500.00, 'Text Preprocessing, Word Embeddings, Seq2Seq Models, BERT.', 'https://miro.medium.com/v2/resize:fit:1200/1*YyGYRTDMcXkilzjGDnZavQ.jpeg', '', 'Advanced', 4.7, 950, 'Teach machines to understand human language.', 11000, 'ML basics, Python', 'New', 'CSE AIML'),
(2, 204, 'Computer Vision with OpenCV', 'Designed for 30 Classes', 9000.00, 'Image Processing, Feature Detection, Object Recognition, GANs.', 'https://content.cloudthat.com/resources/wp-content/uploads/2022/11/OpenCV_ComputerVision.png', 'Dr. Sanjay Gupta', 'Intermediate', 4.6, 880, 'Make computers \"see\" the world.', 10000, 'Python, Linear Algebra', NULL, 'CSE AIML'),
(2, 205, 'Reinforcement Learning Fundamentals', 'Approx. 2.5 Months', 9800.00, 'Markov Decision Processes, Q-Learning, Policy Gradients.', 'https://tse4.mm.bing.net/th?id=OIP.Dij_Ngu3-mAj2ufwkvBdbAHaEK&pid=Api&P=0&h=180\r\n', 'Prof. Neha Sharma', 'Advanced', 4.5, 620, 'Train intelligent agents to learn by doing.', 7500, 'Python, Probability', NULL, 'CSE AIML'),
(3, 301, 'Thermodynamics Fundamentals', 'Approx. 3 Months', 5500.00, 'Laws of Thermodynamics, Cycles, Heat Transfer, Engines.', 'https://st.adda247.com/https://www.careerpower.in/blog/wp-content/uploads/sites/2/2024/01/09162453/thermodynamics-2-1.png', 'Dr. Vikram Singh', 'Beginner', 4.7, 980, 'Understand energy and its transformations.', 11000, 'Physics, Basic Math', 'Popular', 'Mechanical Engineering'),
(3, 302, 'Solid Mechanics and Strength of Materials', 'Designed for 40 Classes', 6800.00, 'Stress, Strain, Bending, Torsion, Failure Theories.', 'https://pictures.abebooks.com/isbn/9780073398235-us.jpg', 'Prof. Sanjana Kapoor', 'Intermediate', 4.8, 1150, 'Analyze how materials behave under load.', 13000, 'Engineering Mechanics', 'Live Class', 'Mechanical Engineering'),
(3, 303, 'Fluid Mechanics and Machinery', 'Approx. 3.5 Months', 6200.00, 'Fluid Properties, Bernoulli\'s Eq., Pumps, Turbines, CFD basics.', 'https://phd.unibo.it/dimsai/en/research/fluid-machinery-energy-systems-mechanics-of-machines-and-industrial-mechanical-plants-1/experiments-and-modelling-in-fluid-machinery/@@images/d268acd6-084c-4855-9e0a-d3c8f00b2204.png', 'Mr. Arjun Reddy', 'Intermediate', 4.6, 850, 'Master the principles of fluid behavior.', 9800, 'Physics, Calculus', NULL, 'Mechanical Engineering'),
(3, 304, 'Machine Design Basics', 'Designed for 35 Classes', 7000.00, 'Design of Joints, Shafts, Gears, Bearings, Springs.', 'https://atachi.co.th/wp-content/uploads/2022/03/Mechanical-Drawing-01-1024x538.jpg', 'Dr. Vikram Singh', 'Intermediate', 4.5, 720, 'Learn to engineer functional machine components.', 8500, 'Solid Mechanics', NULL, 'Mechanical Engineering'),
(3, 305, 'Manufacturing Processes', 'Approx. 4 Months', 6000.00, 'Casting, Forming, Machining, Welding, Additive Manufacturing.', 'https://www.robrosystems.com/robro_blog_img_2.jpg', 'Prof. Sanjana Kapoor', 'All Levels', 4.4, 650, 'Explore how products are made at scale.', 7800, 'None', NULL, 'Mechanical Engineering'),
(4, 401, 'Molecular Biology Techniques', 'Approx. 3 Months', 7000.00, 'DNA extraction, PCR, Gel electrophoresis, Cloning.', 'https://img.freepik.com/premium-photo/molecular-biology-techniques-hd-8k-wallpaper-stock-photographic-image_677426-7264.jpg?w=740', 'Dr. Kavita Sharma', 'Beginner', 4.7, 800, 'Master essential techniques in molecular biology.', 9500, 'Basic Biology, Chemistry', 'Popular', 'Biotechnology Engineering'),
(4, 402, 'Genetic Engineering & Gene Editing', 'Designed for 35 Classes', 8500.00, 'Recombinant DNA technology, CRISPR-Cas9, Gene therapy, Bioethics.', 'https://thumbs.dreamstime.com/b/genetic-engineering-dna-helix-genome-sequencing-outline-hands-concept-change-protein-structure-gene-editing-technology-332821477.jpg', 'Prof. Alok Patel', 'Intermediate', 4.8, 950, 'Reshape life at the genetic level.', 11000, 'Molecular Biology', 'Live Class', 'Biotechnology Engineering'),
(4, 403, 'Bioprocess Engineering Principles', 'Approx. 4 Months', 7800.00, 'Bioreactor design, Fermentation, Downstream processing, Kinetics.', 'https://secure-ecsd.elsevier.com/covers/80/Tango2/large/9781782421672.jpg', 'Ms. Rina Singh', 'Intermediate', 4.6, 700, 'Design and optimize biological production processes.', 8500, 'Thermodynamics, Fluid Mech.', NULL, 'Biotechnology Engineering'),
(4, 404, 'Bioinformatics for Biologists', 'Designed for 30 Classes', 6500.00, 'Sequence alignment, Phylogenetics, Protein structure prediction, Databases.', 'https://www.simplilearn.com/ice9/free_resources_article_thumb/Bioinformatics.jpg', 'Dr. Kavita Sharma', 'All Levels', 4.5, 600, 'Uncover biological insights with computational tools.', 7200, 'Basic Biology, Computer skills', 'New', 'Biotechnology Engineering'),
(4, 405, 'Immunology and Antibody Engineering', 'Approx. 2.5 Months', 9000.00, 'Immune system, Vaccines, Monoclonal antibodies, Therapeutic applications.', 'https://www.analis.com/web/image/607236-942b21dd/Antibodies%20engineering.jpg', 'Prof. Alok Patel', 'Advanced', 4.7, 550, 'Engineer immunity for disease treatment.', 6500, 'Molecular Biology, Cell Biology', NULL, 'Biotechnology Engineering'),
(5, 501, 'Networking Fundamentals for IT Professionals', 'Approx. 3 Months', 6000.00, 'TCP/IP, Routing, Switching, Network Protocols, Troubleshooting.', 'https://itexamtools.com/wp-content/uploads/2021/02/3783-it-networking-fundamentals-for-cisco-ccna-exam.jpg', 'Dr. Sumit Bansal', 'Beginner', 4.7, 1100, 'Build and manage reliable IT networks.', 13000, 'Basic computer usage', 'Popular', 'Information Technology'),
(5, 502, 'System Administration with Linux', 'Designed for 40 Classes', 7500.00, 'Command Line, User Management, File Systems, Scripting, Security.', 'https://assets-global.website-files.com/5b6df8bb681f89c158b48f6b/5d8dd7a40ef690769d10a7dd_Linux-System-Administrator-p-800.jpeg', 'Prof. Meera Joshi', 'Intermediate', 4.8, 980, 'Master the powerful Linux operating system.', 11500, 'Basic OS concepts', 'Live Class', 'Information Technology'),
(5, 503, 'Cloud Computing with AWS (Solutions Architect)', 'Approx. 4 Months', 12000.00, 'EC2, S3, VPC, IAM, Lambda, Serverless Architecture.', 'https://e0.pxfuel.com/wallpapers/209/580/desktop-wallpaper-amazon-web-service-academy-cloud-computing-additional-skill-acquisition-programme-kerala-aws-cloud.jpg', 'Mr. Amit Sharma', 'Intermediate', 4.9, 1500, 'Become a certified AWS Cloud Architect.', 17000, 'Basic networking, OS fundamentals', 'Best Seller', 'Information Technology'),
(5, 504, 'IT Project Management Essentials', 'Designed for 30 Classes', 6500.00, 'Agile, Scrum, Waterfall, Project Planning, Risk Management.', 'https://www.herzing.edu/sites/default/files/styles/fp_960_640/public/2020-09/project-management-skills.jpg.webp?itok=4Nbi1ecA', 'Dr. Sumit Bansal', 'All Levels', 4.5, 780, 'Lead successful IT projects from start to finish.', 9000, 'None', NULL, 'Information Technology'),
(5, 505, 'Database Administration with MySQL', 'Approx. 3 Months', 5800.00, 'Installation, Configuration, Backup, Performance Tuning, Security.', 'https://nuansaglobal.id/wp-content/uploads/2022/04/Pelatihan-Database-Administration-MySQL-1.jpg', 'Prof. Meera Joshi', 'Intermediate', 4.6, 700, 'Ensure your data is always available and secure.', 8200, 'Basic SQL', NULL, 'Information Technology');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
