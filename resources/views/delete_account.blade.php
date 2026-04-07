<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Data Deletion - Realtor One</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0f172a; /* Dark background matching typical admin */
            color: #f8fafc; /* Light text */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #1e293b;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        h1 {
            color: #e2e8f0;
            font-size: 24px;
            margin-bottom: 10px;
            margin-top: 0;
        }
        p {
            color: #94a3b8;
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 25px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        input[type="email"] {
            padding: 12px 15px;
            border-radius: 6px;
            border: 1px solid #334155;
            background-color: #0f172a;
            color: #fff;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
            outline: none;
        }
        input[type="email"]:focus {
            border-color: #3b82f6;
        }
        button {
            background-color: #ef4444;
            color: white;
            padding: 12px 15px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            width: 100%;
        }
        button:hover {
            background-color: #dc2626;
        }
        .alert-success {
            background-color: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.2);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: left;
        }
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Account Data Deletion</h1>
    
    @if(isset($status) && $status === 'success')
        <div class="alert-success">
            <strong>Request Received!</strong> <br>
            Your account data deletion request has been submitted. Our compliance team will process this request and permanently delete your data within 7 business days. You will receive an email confirmation once completed.
        </div>
        <p>If you have any further questions, please contact our support team.</p>
    @else
        <p>
            Submit a request to permanently delete your Realtor One account and all associated personal data from our systems. 
            This action cannot be undone.
        </p>
        
        @if ($errors->any())
            <div class="alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="/delete-account" method="POST">
            @csrf
            <input type="email" name="email" placeholder="Enter your registered email address" required autocomplete="email">
            <button type="submit">Submit Deletion Request</button>
        </form>
    @endif
</div>

</body>
</html>
