<?php
// send-email.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data from JavaScript fetch
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Extract form data
    $branch = isset($data['branch']) ? htmlspecialchars(trim($data['branch'])) : 'General Inquiry';
    $name = isset($data['name']) ? htmlspecialchars(trim($data['name'])) : '';
    $email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
    $subject = isset($data['subject']) ? htmlspecialchars(trim($data['subject'])) : '';
    $message = isset($data['message']) ? htmlspecialchars(trim($data['message'])) : '';
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }
    
    // Determine branch information
    $branch_info = [
        'nuwara-eliya' => [
            'name' => 'Nuwara Eliya Branch',
            'email' => 'nuwaraeliya@natcityhotel.com',
            'phone' => '+94 52 222 3456',
            'whatsapp' => '+94 77 123 4567',
            'address' => '123 Grand Hotel Road, Nuwara Eliya, Sri Lanka'
        ],
        'ella' => [
            'name' => 'Ella Branch',
            'email' => 'ella@natcityhotel.com',
            'phone' => '+94 57 222 7890',
            'whatsapp' => '+94 77 987 6543',
            'address' => '456 Mountain View Road, Ella, Sri Lanka'
        ],
        'both' => [
            'name' => 'Both Branches / General Inquiry',
            'email' => 'info@natcityhotel.com',
            'phone' => 'General Contact',
            'whatsapp' => '+94 77 000 0000',
            'address' => 'Head Office'
        ],
        'General' => [
            'name' => 'General Inquiry',
            'email' => 'info@natcityhotel.com',
            'phone' => 'General Contact',
            'whatsapp' => '+94 77 000 0000',
            'address' => 'Head Office'
        ]
    ];
    
    // Get branch details
    $branch_key = $branch;
    if (!isset($branch_info[$branch_key])) {
        $branch_key = 'General';
    }
    $branch_details = $branch_info[$branch_key];
    
    // Your email configuration - CHANGE THIS TO YOUR EMAIL
    $to = "kavidarshan01@gmail.com"; // ⚠️ CHANGE THIS TO YOUR ACTUAL EMAIL
    $email_subject = "NAT City Hotel Contact: " . ($subject ?: "New Message from $name");
    
    // Email content with HTML formatting
    $email_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #1e3a8a; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { padding: 25px; border: 1px solid #ddd; background-color: #f8fafc; }
            .field { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0; }
            .field:last-child { border-bottom: none; }
            .label { font-weight: bold; color: #1e3a8a; display: inline-block; width: 100px; }
            .value { color: #334155; }
            .branch-info { background-color: #dbeafe; padding: 15px; border-radius: 5px; margin-top: 20px; }
            .footer { background-color: #1e3a8a; color: white; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
            .whatsapp-link { color: #25D366; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>NAT City Hotel - New Contact Form Submission</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Branch:</span>
                    <span class='value'>{$branch_details['name']}</span>
                </div>
                <div class='field'>
                    <span class='label'>From:</span>
                    <span class='value'>$name</span>
                </div>
                <div class='field'>
                    <span class='label'>Email:</span>
                    <span class='value'>$email</span>
                </div>
                <div class='field'>
                    <span class='label'>Subject:</span>
                    <span class='value'>$subject</span>
                </div>
                <div class='field'>
                    <span class='label'>Message:</span><br>
                    <span class='value'>" . nl2br($message) . "</span>
                </div>
                <div class='field'>
                    <span class='label'>Submitted:</span>
                    <span class='value'>" . date('Y-m-d H:i:s') . "</span>
                </div>
                
                <div class='branch-info'>
                    <h3>Branch Contact Information</h3>
                    <p><strong>Email:</strong> {$branch_details['email']}</p>
                    <p><strong>Phone:</strong> {$branch_details['phone']}</p>
                    <p><strong>WhatsApp:</strong> <span class='whatsapp-link'>{$branch_details['whatsapp']}</span></p>
                    <p><strong>Address:</strong> {$branch_details['address']}</p>
                </div>
            </div>
            <div class='footer'>
                <p>This email was sent from the NAT City Hotel website contact form.</p>
                <p>&copy; " . date('Y') . " NAT City Hotel & Restaurant. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: NAT City Hotel Website <noreply@natcityhotel.com>" . "\r\n";
    $headers .= "Reply-To: $name <$email>" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Send email
    if (mail($to, $email_subject, $email_body, $headers)) {
        // Also send a copy to branch-specific email if applicable
        if ($branch_details['email'] != 'info@natcityhotel.com') {
            mail($branch_details['email'], "Copy: " . $email_subject, $email_body, $headers);
        }
        
        // Save to a text file for backup
        $log_entry = date('Y-m-d H:i:s') . " | Branch: $branch | Name: $name | Email: $email | Subject: $subject\n";
        file_put_contents('contact_log.txt', $log_entry, FILE_APPEND);
        
        // Send auto-reply to customer
        $customer_subject = "Thank you for contacting NAT City Hotel";
        $customer_body = "
        Dear $name,
        
        Thank you for contacting NAT City Hotel {$branch_details['name']}. 
        We have received your message and will get back to you within 24 hours.
        
        Your inquiry details:
        Subject: $subject
        Branch: {$branch_details['name']}
        
        For immediate assistance, you can also WhatsApp us at: {$branch_details['whatsapp']}
        
        Best regards,
        NAT City Hotel Team
        {$branch_details['email']}
        {$branch_details['phone']}
        ";
        
        $customer_headers = "From: NAT City Hotel <{$branch_details['email']}>\r\n";
        mail($email, $customer_subject, $customer_body, $customer_headers);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Thank you! Your message has been sent successfully. We will get back to you within 24 hours. You will also receive a confirmation email shortly.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sorry, there was an error sending your message. Please try again later or contact us via WhatsApp.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>