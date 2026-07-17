New job application

Job: {{ $application->jobListing?->title ?? 'Unknown job' }}
Name: {{ $application->name }}
Email: {{ $application->email }}
Phone: {{ $application->phone ?: 'Not provided' }}
Resume: {{ $application->resume_path ?: 'Not provided' }}

Cover letter:
{{ $application->cover_letter ?: 'Not provided' }}
