<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Chatbot</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial; padding: 20px; }
        #chatbox { border: 1px solid #ccc; padding: 10px; max-width: 500px; margin: auto; }
        .user { text-align: right; color: blue; }
        .bot { text-align: left; color: green; }
    </style>
</head>
<body>
    <div id="chatbox">
        <div id="messages"></div>
        <input type="text" id="userInput" placeholder="Escribe tu pregunta..." />
        <button onclick="sendMessage()">Enviar</button>
    </div>

    <script>
        function sendMessage() {
            let message = document.getElementById('userInput').value;
            if (!message) return;

            document.getElementById('messages').innerHTML += '<div class="user">' + message + '</div>';

            fetch('/chatbot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ message: message })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('messages').innerHTML += '<div class="bot">' + data.response + '</div>';
                document.getElementById('userInput').value = '';
            });
        }
    </script>
</body>
</html>

