New contact message

Name: {{ $messageModel->name }}
Email: {{ $messageModel->email }}
Subject: {{ $messageModel->subject ?: 'No subject' }}

Message:
{{ $messageModel->message }}
