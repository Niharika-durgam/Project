<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'client') {
    header("Location: ../index.php");
    exit();
}

$client_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $budget = $_POST['budget'];
    $deadline = $_POST['deadline'];
    $skills = $_POST['skills'];
    $experience = $_POST['experienceLevel'];
    $projectType = $_POST['projectType'];

    // Handle multiple attachments
    $attachmentPaths = [];
    $uploadDir = "../uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!empty($_FILES['attachments']['name'][0])) {
        foreach ($_FILES['attachments']['tmp_name'] as $index => $tmpName) {
            $originalName = basename($_FILES['attachments']['name'][$index]);
            $uniqueName = time() . '' . preg_replace("/[^a-zA-Z0-9.]/", "", $originalName);
            $targetPath = $uploadDir . $uniqueName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $attachmentPaths[] = $targetPath;
            }
        }
    }

    $attachmentsJson = json_encode($attachmentPaths);

    // Insert into jobs table
    $stmt = $conn->prepare("INSERT INTO jobs (client_id, title, description, budget, deadline, skills, experience_level, project_type, attachments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssss", $client_id, $title, $desc, $budget, $deadline, $skills, $experience, $projectType, $attachmentsJson);

    if ($stmt->execute()) {
        $success_message = "<p class='success-message'>✅ Job posted successfully!</p>";
    } else {
        $error_message = "<p class='error-message'>❌ Error: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a New Job</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --error-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --light-gray: #e9ecef;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark-color);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
            text-align: center;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .back-link i {
            margin-right: 5px;
        }

        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: var(--light-color);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .form-section h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light-gray);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-color);
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .radio-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 10px 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid var(--light-gray);
            transition: all 0.3s;
        }

        .radio-label:hover {
            border-color: var(--primary-color);
        }

        .radio-label input {
            margin-right: 8px;
        }

        .file-upload {
            position: relative;
            margin-bottom: 10px;
        }

        .file-upload input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px;
            border: 2px dashed var(--light-gray);
            border-radius: 6px;
            background: white;
            color: var(--gray-color);
            text-align: center;
            transition: all 0.3s;
        }

        .file-upload-label i {
            font-size: 36px;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .file-upload-label:hover {
            border-color: var(--primary-color);
            background: rgba(67, 97, 238, 0.05);
        }

        .file-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .file-item {
            background: white;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid var(--light-gray);
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .file-item i {
            margin-right: 5px;
            color: var(--gray-color);
        }

        .skills-input-container {
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            padding: 10px;
            background: white;
        }

        #skillsInput {
            border: none;
            padding: 8px;
            margin-top: 5px;
            width: 100%;
        }

        #skillsInput:focus {
            outline: none;
            box-shadow: none;
        }

        .skills-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 5px;
        }

        .tag {
            background-color: var(--primary-color);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
        }

        .tag::after {
            content: '×';
            margin-left: 5px;
            cursor: pointer;
        }

        .form-actions {
            text-align: center;
            margin-top: 30px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .success-message {
            color: var(--success-color);
            background: rgba(76, 201, 240, 0.1);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid var(--success-color);
        }

        .error-message {
            color: var(--error-color);
            background: rgba(247, 37, 133, 0.1);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid var(--error-color);
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
        }
		.back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            color: var(--primary);
            text-decoration: none;
            border: 1px solid var(--primary);
            border-radius: 5px;
            transition: var(--transition);
        }

        .back-link:hover {
            background-color: var(--primary-light);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Post a New Job</h1>
        
        <?php 
        if (isset($success_message)) {
            echo $success_message;
        }
        if (isset($error_message)) {
            echo $error_message;
        }
        ?>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-section">
                <h3>Project Details</h3>

                <div class="form-group">
                    <label for="title">Project Title</label>
                    <input type="text" id="title" name="title" required placeholder="e.g. Build a personal portfolio website">
                </div>

                <div class="form-group">
                    <label for="description">Project Description</label>
                    <textarea id="description" name="description" rows="6" required
                        placeholder="Describe your project in detail..."></textarea>
                </div>

                <div class="form-group">
                    <label for="budget">Budget (₹)</label>
                    <input type="number" id="budget" name="budget" required placeholder="Enter your budget">
                </div>

                <div class="form-group">
                    <label for="deadline">Deadline</label>
                    <input type="date" id="deadline" name="deadline" required>
                </div>

                <div class="form-group">
                    <label for="skills">Required Skills</label>
                    <div class="skills-input-container">
                        <div class="skills-tags" id="skillsTags"></div>
                        <input type="text" id="skillsInput" placeholder="Type a skill and press Enter">
                    </div>
                    <input type="hidden" id="skills" name="skills">
                </div>

                <div class="form-group">
                    <label for="attachments">Attachments</label>
                    <div class="file-upload">
                        <input type="file" id="attachments" name="attachments[]" multiple
                            accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                        <label for="attachments" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Drop files here or click to upload</span>
                        </label>
                    </div>
                    <div id="fileList" class="file-list"></div>
                </div>
            </div>

            <div class="form-section">
                <h3>Additional Information</h3>

                <div class="form-group">
                    <label>Experience Level</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="experienceLevel" value="entry" required>
                            <span>Entry Level</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="experienceLevel" value="intermediate">
                            <span>Intermediate</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="experienceLevel" value="expert">
                            <span>Expert</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Project Type</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="projectType" value="one-time" required>
                            <span>One-time project</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="projectType" value="ongoing">
                            <span>Ongoing project</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Post Job</button>
            </div>
        </form>
		 <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

    <script>
        // Skills Tags Functionality
        document.getElementById("skillsInput").addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                const input = e.target;
                const tag = input.value.trim();
                if (tag !== "") {
                    addSkillTag(tag);
                    input.value = "";
                }
            }
        });

        function addSkillTag(tag) {
            const tagSpan = document.createElement("span");
            tagSpan.textContent = tag;
            tagSpan.classList.add("tag");
            
            // Add click event to remove tag
            tagSpan.addEventListener("click", function() {
                this.remove();
                updateSkillsHiddenInput();
            });
            
            document.getElementById("skillsTags").appendChild(tagSpan);
            updateSkillsHiddenInput();
        }

        function updateSkillsHiddenInput() {
            const tags = Array.from(document.querySelectorAll(".skills-tags .tag"))
                .map(tag => tag.textContent.replace('×', '').trim());
            document.getElementById("skills").value = tags.join(",");
        }

        // File Upload Display
        document.getElementById("attachments").addEventListener("change", function(e) {
            const fileList = document.getElementById("fileList");
            fileList.innerHTML = "";
            
            Array.from(e.target.files).forEach(file => {
                const fileItem = document.createElement("div");
                fileItem.classList.add("file-item");
                
                let iconClass = "fa-file";
                if (file.type.includes("image")) {
                    iconClass = "fa-image";
                } else if (file.type.includes("pdf")) {
                    iconClass = "fa-file-pdf";
                } else if (file.type.includes("word")) {
                    iconClass = "fa-file-word";
                }
                
                fileItem.innerHTML = <i class="fas ${iconClass}"></i> ${file.name};
                fileList.appendChild(fileItem);
            });
        });

        // Set minimum date for deadline (today)
        document.getElementById("deadline").min = new Date().toISOString().split("T")[0];
    </script>
</body>
</html>