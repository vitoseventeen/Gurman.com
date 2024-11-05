<?php
session_start();
// Подключение к базе данных
include('config.php');

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Обработка формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получение данных из формы
    $recipeId = $_POST["recipe_id"];
    $userId = $_SESSION["id"]; // Предполагается, что у вас есть система аутентификации с сессиями
    $username = $_SESSION["username"]; // Предполагается, что у вас есть система аутентификации с сессиями
    $commentText = trim($_POST["comment"]); // Удаление пробелов из начала и конца строки
    $commentText = substr($commentText, 0, 1000); // Ограничение до 1000 символов

    // Замена переводов строки на пробелы
    $commentText = str_replace(array("\n", "\r"), ' ', $commentText);

    // Проверка наличия текста комментария
    if (!empty($commentText)) {
        // Фильтрация и экранирование данных для предотвращения SQL инъекций
        $commentText = mysqli_real_escape_string($conn, $commentText);

        // Подготовка и выполнение запроса к базе данных для вставки комментария
        $stmt = $conn->prepare("INSERT INTO comments (recipe_id, user_id, username, comment_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $recipeId, $userId, $username, $commentText);
        $stmt->execute();
        $stmt->close();

        // Перенаправление обратно на страницу рецепта или куда-то еще
        header("Location: recipe_element.php?id=" . $recipeId);
        exit();
    } else {
        // Если текст комментария пуст или превышает 1000 символов, отправить пользователя обратно с сообщением об ошибке
        header("Location: recipe_element.php?id=" . $recipeId . "&error=invalid_comment");
        exit();
    }
}

// Закрытие соединения с базой данных
$conn->close();
?>
