<?php
session_start();
include '../conn.php';

// Check if center is logged in
if (!isset($_SESSION['center_id'])) {
    header("Location: center_login.php");
    exit();
}

$center_id = $_SESSION['center_id'];
$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;

// Get exam details
$exam_query = "SELECT es.*, cd.center_name, c.course_name, m.module_name 
               FROM exam_schedule es
               JOIN center_details cd ON es.center_id = cd.id
               JOIN courses c ON es.course_id = c.id
               LEFT JOIN modules m ON es.module_id = m.id
               WHERE es.id = ? AND es.center_id = ?";
$exam_stmt = $conn->prepare($exam_query);
$exam_stmt->bind_param("ii", $exam_id, $center_id);
$exam_stmt->execute();
$exam_result = $exam_stmt->get_result();

if ($exam_result->num_rows == 0) {
    die("Exam not found or you don't have permission to access it.");
}

$exam = $exam_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paper_name = $_POST['paper_name'];
    $total_questions = $_POST['total_questions'];
    $marks_per_question = $_POST['marks_per_question'];
    $negative_marking = $_POST['negative_marking'];
    $instructions = $_POST['instructions'];
    
    // Insert question paper
    $paper_stmt = $conn->prepare("INSERT INTO question_papers (
        exam_id, center_id, paper_name, total_questions, marks_per_question,
        negative_marking, instructions, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $paper_stmt->bind_param(
        "iisiidss",
        $exam_id, $center_id, $paper_name, $total_questions, $marks_per_question,
        $negative_marking, $instructions, $center_id
    );
    
    if ($paper_stmt->execute()) {
        $paper_id = $conn->insert_id;
        
        // Insert questions
        $question_count = $_POST['question_count'] ?? 0;
        
        for ($i = 1; $i <= $question_count; $i++) {
            if (isset($_POST['question_text'][$i]) && !empty($_POST['question_text'][$i])) {
                $question_text = $_POST['question_text'][$i];
                $question_type = $_POST['question_type'][$i] ?? 'MCQ';
                $marks = $_POST['marks'][$i] ?? $marks_per_question;
                $difficulty = $_POST['difficulty'][$i] ?? 'Medium';
                
                $question_stmt = $conn->prepare("INSERT INTO questions (
                    question_paper_id, question_text, question_type, marks, 
                    difficulty_level, serial_number
                ) VALUES (?, ?, ?, ?, ?, ?)");
                
                $question_stmt->bind_param(
                    "issisi",
                    $paper_id, $question_text, $question_type, $marks, $difficulty, $i
                );
                
                if ($question_stmt->execute()) {
                    $question_id = $conn->insert_id;
                    
                    // Insert options for MCQ questions
                    if ($question_type == 'MCQ') {
                        for ($j = 1; $j <= 4; $j++) {
                            if (isset($_POST['option_text'][$i][$j]) && !empty($_POST['option_text'][$i][$j])) {
                                $option_text = $_POST['option_text'][$i][$j];
                                $is_correct = ($_POST['correct_option'][$i] == $j) ? 1 : 0;
                                $option_letter = chr(64 + $j); // A, B, C, D
                                
                                $option_stmt = $conn->prepare("INSERT INTO question_options (
                                    question_id, option_text, is_correct, option_letter
                                ) VALUES (?, ?, ?, ?)");
                                
                                $option_stmt->bind_param(
                                    "isis",
                                    $question_id, $option_text, $is_correct, $option_letter
                                );
                                
                                $option_stmt->execute();
                            }
                        }
                    }
                }
            }
        }
        
        // Update paper status to Published
        $update_stmt = $conn->prepare("UPDATE question_papers SET status = 'Published' WHERE id = ?");
        $update_stmt->bind_param("i", $paper_id);
        $update_stmt->execute();
        
        // Send notification to admin
        $center_name = $exam['center_name'];
        $notification_title = "Question Paper Created";
        $notification_message = "Center '$center_name' has created question paper '$paper_name' for exam '{$exam['exam_name']}'.";
        
        $notify_stmt = $conn->prepare("INSERT INTO notifications (
            notification_type, title, message, sender_id, 
            receiver_type, receiver_id, related_id
        ) VALUES ('Question_Paper', ?, ?, ?, 'Admin', 1, ?)");
        
        $notify_stmt->bind_param(
            "ssi",
            $notification_title,
            $notification_message,
            $center_id,
            $paper_id
        );
        
        $notify_stmt->execute();
        
        $success = "Question paper created successfully! Notification sent to admin.";
        header("Location: view-question-paper.php?id=" . $paper_id);
        exit();
    } else {
        $error = "Failed to create question paper. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Question Paper - <?php echo $exam['exam_name']; ?></title>
    
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --success: #06d6a0;
            --warning: #ffd166;
            --danger: #ef476f;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .paper-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 0 auto;
            max-width: 1200px;
        }
        
        .paper-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 40px;
        }
        
        .exam-info {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .info-item {
            background: rgba(255,255,255,0.2);
            padding: 10px 15px;
            border-radius: 8px;
        }
        
        .paper-body {
            padding: 40px;
        }
        
        .form-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 5px solid var(--primary);
        }
        
        .section-title {
            color: var(--primary);
            font-weight: 600;
            font-size: 1.3rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .question-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        
        .question-card:hover {
            border-color: var(--primary);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .question-number {
            background: var(--primary);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        
        .option-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        
        .option-item.selected {
            border-color: var(--success);
            background: rgba(6, 214, 160, 0.1);
        }
        
        .option-letter {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #6c757d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .btn-add-question {
            background: linear-gradient(135deg, var(--success) 0%, #05c191 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-add-question:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(6, 214, 160, 0.3);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
        }
        
        .remove-btn {
            background: var(--danger);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .remove-btn:hover {
            transform: scale(1.1);
        }
        
        .question-counter {
            background: var(--warning);
            color: #333;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
    </style>
</head>
<body>
    <div class="paper-container">
        <div class="paper-header">
            <h1><i class="fas fa-file-alt me-2"></i>Create Question Paper</h1>
            <p>Design question paper for <?php echo htmlspecialchars($exam['exam_name']); ?></p>
            
            <div class="exam-info">
                <h4><i class="fas fa-info-circle me-2"></i>Exam Details</h4>
                <div class="info-grid">
                    <div class="info-item"><strong>Center:</strong> <?php echo htmlspecialchars($exam['center_name']); ?></div>
                    <div class="info-item"><strong>Course:</strong> <?php echo htmlspecialchars($exam['course_name']); ?></div>
                    <div class="info-item"><strong>Module:</strong> <?php echo htmlspecialchars($exam['module_name']); ?></div>
                    <div class="info-item"><strong>Date:</strong> <?php echo date('d M Y', strtotime($exam['exam_date'])); ?></div>
                    <div class="info-item"><strong>Time:</strong> <?php echo date('h:i A', strtotime($exam['start_time'])); ?></div>
                    <div class="info-item"><strong>Duration:</strong> <?php echo $exam['duration_minutes']; ?> minutes</div>
                </div>
            </div>
        </div>
        
        <div class="paper-body">
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="questionPaperForm">
                <!-- Paper Details -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-cog"></i>Paper Configuration</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Paper Name</label>
                            <input type="text" class="form-control" name="paper_name" 
                                   value="<?php echo $exam['exam_name'] . ' Question Paper'; ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Total Questions</label>
                            <input type="number" class="form-control" name="total_questions" 
                                   value="20" min="1" max="100" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Marks per Question</label>
                            <input type="number" class="form-control" name="marks_per_question" 
                                   value="1" min="1" step="0.5" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Negative Marking (per wrong answer)</label>
                            <input type="number" class="form-control" name="negative_marking" 
                                   value="0.25" min="0" step="0.05">
                            <small class="text-muted">Set to 0 for no negative marking</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Total Marks (Auto-calculated)</label>
                            <input type="text" class="form-control" id="totalMarks" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label>Paper Instructions</label>
                            <textarea class="form-control" name="instructions" rows="3">
1. All questions are compulsory.
2. Read questions carefully before answering.
3. Use black or blue pen only.
                            </textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Questions Section -->
                <div class="form-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="section-title"><i class="fas fa-question-circle"></i>Questions</h3>
                        <div class="question-counter" id="questionCounter">0 Questions Added</div>
                    </div>
                    
                    <div id="questionsContainer">
                        <!-- Questions will be added here dynamically -->
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="button" class="btn-add-question" onclick="addQuestion()">
                            <i class="fas fa-plus"></i> Add Question
                        </button>
                    </div>
                </div>
                
                <input type="hidden" name="question_count" id="questionCount" value="0">
                
                <div class="text-center">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Save Question Paper & Notify Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let questionCount = 0;
        const letters = ['A', 'B', 'C', 'D'];
        
        function addQuestion() {
            questionCount++;
            document.getElementById('questionCount').value = questionCount;
            updateQuestionCounter();
            
            const container = document.getElementById('questionsContainer');
            const questionHTML = `
                <div class="question-card" id="questionCard${questionCount}">
                    <div class="question-header">
                        <div class="question-number">${questionCount}</div>
                        <button type="button" class="remove-btn" onclick="removeQuestion(${questionCount})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <label>Question Text</label>
                        <textarea class="form-control" name="question_text[${questionCount}]" 
                                  placeholder="Enter question text..." required></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Question Type</label>
                            <select class="form-control" name="question_type[${questionCount}]" 
                                    onchange="toggleOptions(${questionCount}, this.value)">
                                <option value="MCQ">Multiple Choice (MCQ)</option>
                                <option value="True_False">True/False</option>
                                <option value="Short_Answer">Short Answer</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Marks</label>
                            <input type="number" class="form-control" name="marks[${questionCount}]" 
                                   value="${document.getElementsByName('marks_per_question')[0].value}" min="1" step="0.5">
                        </div>
                        <div class="col-md-4">
                            <label>Difficulty</label>
                            <select class="form-control" name="difficulty[${questionCount}]">
                                <option value="Easy">Easy</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="Hard">Hard</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="optionsContainer${questionCount}">
                        <!-- Options for MCQ will be added here -->
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', questionHTML);
            toggleOptions(questionCount, 'MCQ');
        }
        
        function toggleOptions(questionId, questionType) {
            const container = document.getElementById(`optionsContainer${questionId}`);
            
            if (questionType === 'MCQ') {
                container.innerHTML = `
                    <label>Options (Select correct answer)</label>
                    <div class="options-grid" id="mcqOptions${questionId}">
                        ${letters.map((letter, index) => `
                            <div class="option-item" onclick="selectOption(${questionId}, ${index + 1})" 
                                 id="option${questionId}_${index + 1}">
                                <div class="option-letter">${letter}</div>
                                <input type="text" class="form-control" 
                                       name="option_text[${questionId}][${index + 1}]" 
                                       placeholder="Option ${letter}" required 
                                       onclick="event.stopPropagation()">
                                <input type="radio" name="correct_option[${questionId}]" 
                                       value="${index + 1}" style="display: none;">
                            </div>
                        `).join('')}
                    </div>
                `;
            } else if (questionType === 'True_False') {
                container.innerHTML = `
                    <label>Correct Answer</label>
                    <div class="options-grid">
                        <div class="option-item" onclick="selectTrueFalse(${questionId}, 'True')" 
                             id="tf${questionId}_true">
                            <div class="option-letter">T</div>
                            <span>True</span>
                            <input type="radio" name="correct_option[${questionId}]" 
                                   value="True" style="display: none;">
                        </div>
                        <div class="option-item" onclick="selectTrueFalse(${questionId}, 'False')" 
                             id="tf${questionId}_false">
                            <div class="option-letter">F</div>
                            <span>False</span>
                            <input type="radio" name="correct_option[${questionId}]" 
                                   value="False" style="display: none;">
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Students will write their answer in the answer sheet.
                    </div>
                `;
            }
        }
        
        function selectOption(questionId, optionNum) {
            // Remove selected class from all options
            for (let i = 1; i <= 4; i++) {
                const option = document.getElementById(`option${questionId}_${i}`);
                if (option) {
                    option.classList.remove('selected');
                    option.querySelector('input[type="radio"]').checked = false;
                }
            }
            
            // Add selected class to clicked option
            const selectedOption = document.getElementById(`option${questionId}_${optionNum}`);
            if (selectedOption) {
                selectedOption.classList.add('selected');
                selectedOption.querySelector('input[type="radio"]').checked = true;
            }
        }
        
        function selectTrueFalse(questionId, value) {
            // Remove selected class from both
            document.getElementById(`tf${questionId}_true`).classList.remove('selected');
            document.getElementById(`tf${questionId}_false`).classList.remove('selected');
            
            // Add selected class to clicked one
            const selected = document.getElementById(`tf${questionId}_${value.toLowerCase()}`);
            if (selected) {
                selected.classList.add('selected');
                selected.querySelector('input[type="radio"]').checked = true;
            }
        }
        
        function removeQuestion(questionId) {
            const card = document.getElementById(`questionCard${questionId}`);
            if (card) {
                card.remove();
                questionCount--;
                document.getElementById('questionCount').value = questionCount;
                updateQuestionCounter();
                renumberQuestions();
            }
        }
        
        function renumberQuestions() {
            const questions = document.querySelectorAll('.question-card');
            questions.forEach((card, index) => {
                const questionNum = index + 1;
                card.querySelector('.question-number').textContent = questionNum;
                
                // Update all input names with new question number
                const textarea = card.querySelector('textarea[name^="question_text"]');
                if (textarea) {
                    textarea.name = `question_text[${questionNum}]`;
                }
                
                const typeSelect = card.querySelector('select[name^="question_type"]');
                if (typeSelect) {
                    typeSelect.name = `question_type[${questionNum}]`;
                    typeSelect.onchange = function() {
                        toggleOptions(questionNum, this.value);
                    };
                }
                
                const marksInput = card.querySelector('input[name^="marks"]');
                if (marksInput) {
                    marksInput.name = `marks[${questionNum}]`;
                }
                
                const difficultySelect = card.querySelector('select[name^="difficulty"]');
                if (difficultySelect) {
                    difficultySelect.name = `difficulty[${questionNum}]`;
                }
                
                // Update options if MCQ
                const optionsContainer = document.getElementById(`optionsContainer${index + 1}`);
                if (optionsContainer && optionsContainer.id) {
                    optionsContainer.id = `optionsContainer${questionNum}`;
                    
                    const mcqOptions = optionsContainer.querySelector(`#mcqOptions${index + 1}`);
                    if (mcqOptions) {
                        mcqOptions.id = `mcqOptions${questionNum}`;
                        
                        // Update option items
                        for (let i = 1; i <= 4; i++) {
                            const optionItem = document.getElementById(`option${index + 1}_${i}`);
                            if (optionItem) {
                                optionItem.id = `option${questionNum}_${i}`;
                                optionItem.onclick = function() {
                                    selectOption(questionNum, i);
                                };
                                
                                const optionInput = optionItem.querySelector('input[name^="option_text"]');
                                if (optionInput) {
                                    optionInput.name = `option_text[${questionNum}][${i}]`;
                                }
                                
                                const radioInput = optionItem.querySelector('input[type="radio"]');
                                if (radioInput) {
                                    radioInput.name = `correct_option[${questionNum}]`;
                                }
                            }
                        }
                    }
                    
                    // Update True/False items
                    const trueItem = document.getElementById(`tf${index + 1}_true`);
                    if (trueItem) {
                        trueItem.id = `tf${questionNum}_true`;
                        trueItem.onclick = function() {
                            selectTrueFalse(questionNum, 'True');
                        };
                    }
                    
                    const falseItem = document.getElementById(`tf${index + 1}_false`);
                    if (falseItem) {
                        falseItem.id = `tf${questionNum}_false`;
                        falseItem.onclick = function() {
                            selectTrueFalse(questionNum, 'False');
                        };
                    }
                }
            });
        }
        
        function updateQuestionCounter() {
            document.getElementById('questionCounter').textContent = 
                `${questionCount} Question${questionCount !== 1 ? 's' : ''} Added`;
        }
        
        function calculateTotalMarks() {
            const totalQuestions = document.getElementsByName('total_questions')[0].value;
            const marksPerQuestion = document.getElementsByName('marks_per_question')[0].value;
            const totalMarks = totalQuestions * marksPerQuestion;
            document.getElementById('totalMarks').value = totalMarks;
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            calculateTotalMarks();
            
            // Add first 5 questions by default
            for (let i = 0; i < 5; i++) {
                addQuestion();
            }
            
            // Auto-calculate total marks when inputs change
            document.getElementsByName('total_questions')[0].addEventListener('input', calculateTotalMarks);
            document.getElementsByName('marks_per_question')[0].addEventListener('input', calculateTotalMarks);
        });
        
        // Form validation
        document.getElementById('questionPaperForm').addEventListener('submit', function(e) {
            const totalQuestions = parseInt(document.getElementsByName('total_questions')[0].value);
            
            if (questionCount < totalQuestions) {
                e.preventDefault();
                alert(`Please add at least ${totalQuestions} questions. Currently added: ${questionCount}`);
                return false;
            }
            
            // Validate that all MCQ questions have correct option selected
            for (let i = 1; i <= questionCount; i++) {
                const questionType = document.querySelector(`select[name="question_type[${i}]"]`);
                if (questionType && questionType.value === 'MCQ') {
                    const correctOption = document.querySelector(`input[name="correct_option[${i}]"]:checked`);
                    if (!correctOption) {
                        e.preventDefault();
                        alert(`Please select correct option for Question ${i}`);
                        return false;
                    }
                }
            }
            
            return true;
        });
    </script>
</body>
</html>