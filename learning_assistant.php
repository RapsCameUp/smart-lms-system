<?php
/**
 * AI Learning Assistant API (Mathematics & Physical Science)
 *
 * Integration: POST JSON from student.php / librarian.php when "Learning assistant" is ON.
 * Uses OpenRouter (not OpenAI directly). Set OPENROUTER_API_KEY in server environment.
 * If unset, a development fallback key may be used (see below).
 * Optional: OPENROUTER_MODEL (default openai/gpt-4o-mini), OPENROUTER_HTTP_REFERER, OPENROUTER_APP_TITLE
 */

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'OK']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.']);
    exit();
}

/**
 * Read param from GET, POST, or JSON body (same pattern as research_assistant.php).
 */
function la_get_param(string $name, $default = null) {
    if (isset($_GET[$name])) {
        return $_GET[$name];
    }
    if (isset($_POST[$name])) {
        return $_POST[$name];
    }
    static $json_body = null;
    if ($json_body === null) {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json_body = json_decode($raw, true);
            if (!is_array($json_body)) {
                $json_body = [];
            }
        } else {
            $json_body = [];
        }
    }
    return $json_body[$name] ?? $default;
}

$message = trim((string) la_get_param('message', ''));
if ($message === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'message is required.']);
    exit();
}

$assistant_mode = strtolower((string) la_get_param('assistant_mode', 'tutor'));
$allowed_modes = ['tutor', 'homework_help', 'quick_answer', 'exam_prep'];
if (!in_array($assistant_mode, $allowed_modes, true)) {
    $assistant_mode = 'tutor';
}

$subject = strtolower((string) la_get_param('subject', 'mathematics'));
if (!in_array($subject, ['mathematics', 'physical_science'], true)) {
    $subject = 'mathematics';
}

$voice_lesson = (bool) la_get_param('voice_lesson', false);
$conversation_tail = la_get_param('conversation_tail', null);
if (!is_array($conversation_tail)) {
    $conversation_tail = [];
}

// OPENROUTER_API_KEY from env; optional fallback only for local/dev when env is not set.
$apiKey = getenv('OPENROUTER_API_KEY') ?: 'APIKEY_Here';
if ($apiKey === '') {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'OPENROUTER_API_KEY is not configured on the server.',
    ]);
    exit();
}

$model = getenv('OPENROUTER_MODEL') ?: 'openai/gpt-4o-mini';
$httpReferer = getenv('OPENROUTER_HTTP_REFERER') ?: (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] : 'https://localhost');
$appTitle = getenv('OPENROUTER_APP_TITLE') ?: 'Smart Library Learning Assistant';

// --- Session context for follow-ups ---
if (!isset($_SESSION['learning_ctx']) || !is_array($_SESSION['learning_ctx'])) {
    $_SESSION['learning_ctx'] = [];
}
$ctx = &$_SESSION['learning_ctx'];

$subject_label = $subject === 'physical_science' ? 'Physical Science' : 'Mathematics';

$base_system = "You are an educational tutor for Mathematics and Physical Science only.\n"
    . "Always teach clearly. Show reasoning. Avoid skipping steps. Use student-friendly language.\n"
    . "Prefer structured explanations using the exact section headings requested.\n"
    . "If the user asks about topics outside maths or physical science, briefly say you only help with those two subjects and suggest they use the library chatbot for other questions.\n";

$subject_addon = $subject === 'mathematics'
    ? "For Mathematics: show calculations step-by-step; define variables; state formulas before substituting numbers.\n"
    : "For Physical Science: name physical concepts; give formulas with each variable defined; include a short real-world example when helpful.\n";

$structure = "### Concept Explanation\n...\n### Formula Used\n...\n### Step-by-Step Solution\n...\n### Final Answer\n...\n### Extra Tip\n...\n";

$mode_instructions = [
    'tutor' => "Mode: Tutor. Explain the concept step-by-step. End with one short check question for the student.\n",
    'homework_help' => "Mode: Homework help. Provide a full worked solution with all steps; encourage understanding, not just the answer.\n",
    'quick_answer' => "Mode: Quick answer. Keep the overall reply concise (roughly 2–4 short paragraphs or bullet groups) but still include the required section headings and do not skip core reasoning.\n",
    'exam_prep' => "Mode: Exam prep. Include 3–5 practice questions in ### Step-by-Step Solution or a subsection \"### Practice Questions\", then provide answers in \"### Final Answer\" or \"### Extra Tip\" clearly separated.\n",
];

$voice_addon = '';
if ($voice_lesson) {
    $voice_addon = "Voice lesson format: In ### Concept Explanation include a brief Introduction. Ensure ### Step-by-Step Solution contains a worked Example. Add one Short quiz question in ### Extra Tip (with the answer on the next line).\n";
}

$output_rule = "You MUST use these Markdown headings exactly (even if a section is brief):\n" . $structure;

$system_prompt = $base_system . $subject_addon . ($mode_instructions[$assistant_mode] ?? '') . $voice_addon . $output_rule;

// Inject prior context for follow-ups
$context_block = '';
if (!empty($ctx['last_assistant_raw']) && is_string($ctx['last_assistant_raw'])) {
    $context_block = "\n\n[Previous assistant reply for reference — user may ask follow-ups like \"explain step 2\"]\n"
        . mb_substr($ctx['last_assistant_raw'], 0, 6000) . "\n";
}

$user_content = $message . $context_block;

$messages = [
    ['role' => 'system', 'content' => $system_prompt],
];
foreach ($conversation_tail as $turn) {
    if (!is_array($turn)) {
        continue;
    }
    $r = $turn['role'] ?? '';
    $c = trim((string) ($turn['content'] ?? ''));
    if (($r === 'user' || $r === 'assistant') && $c !== '') {
        $messages[] = ['role' => $r, 'content' => $c];
    }
}
$messages[] = ['role' => 'user', 'content' => $user_content];

/**
 * Call OpenRouter chat completions.
 */
function la_openrouter_call(string $apiKey, string $model, string $httpReferer, string $appTitle, array $messages, float $temperature = 0.4, int $maxTokens = 2500): string {
    $payload = json_encode([
        'model'       => $model,
        'messages'    => $messages,
        'temperature' => $temperature,
        'max_tokens'  => $maxTokens,
    ]);

    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: ' . $httpReferer,
            'X-Title: ' . $appTitle,
        ],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 90,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new Exception('OpenRouter connection error: ' . $err);
    }
    $data = json_decode($response, true);
    if ($code < 200 || $code >= 300) {
        $msg = is_array($data) ? json_encode($data) : $response;
        throw new Exception('OpenRouter HTTP ' . $code . ': ' . mb_substr($msg, 0, 500));
    }
    if (!is_array($data) || !isset($data['choices'][0]['message']['content'])) {
        throw new Exception('Invalid OpenRouter response format');
    }
    return trim((string) $data['choices'][0]['message']['content']);
}

/**
 * Heuristic: response needs regeneration for educational quality.
 */
function la_needs_regeneration(string $text, string $assistant_mode): bool {
    $len = mb_strlen(preg_replace('/\s+/', '', $text));
    if ($len < 80) {
        return true;
    }
    $has_heading = (bool) preg_match('/###\s*(Concept Explanation|Step-by-Step|Final Answer)/i', $text);
    if (!$has_heading && $assistant_mode !== 'quick_answer') {
        return true;
    }
    if ($assistant_mode === 'homework_help' && !preg_match('/###\s*Final Answer/i', $text)) {
        return true;
    }
    return false;
}

/**
 * Parse ### sections into associative array.
 */
function la_parse_sections(string $text): array {
    $sections = [];
    $pattern = '/###\s*([^\n]+)\n([\s\S]*?)(?=###\s|$)/';
    if (preg_match_all($pattern, $text, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $key = trim($match[1]);
            $sections[$key] = trim($match[2]);
        }
    }
    return $sections;
}

/**
 * Plain text for TTS (strip markdown noise lightly).
 */
function la_speak_text(string $markdown): string {
    $t = preg_replace('/```[\s\S]*?```/', ' ', $markdown);
    $t = preg_replace('/`([^`]+)`/', '$1', $t);
    $t = preg_replace('/#{1,6}\s*/', '', $t);
    $t = preg_replace('/\*\*([^*]+)\*\*/', '$1', $t);
    $t = preg_replace('/\*([^*]+)\*/', '$1', $t);
    $t = strip_tags($t);
    return trim(preg_replace('/\s+/', ' ', $t));
}

try {
    $assistant_raw = la_openrouter_call($apiKey, $model, $httpReferer, $appTitle, $messages);

    if (la_needs_regeneration($assistant_raw, $assistant_mode)) {
        $retry_messages = $messages;
        $retry_messages[] = [
            'role'    => 'user',
            'content' => 'Provide step-by-step educational explanation suitable for students. Use all required ### headings. Do not skip steps.',
        ];
        $assistant_raw = la_openrouter_call($apiKey, $model, $httpReferer, $appTitle, $retry_messages, 0.3, 2800);
    }

    $structured = la_parse_sections($assistant_raw);
    $speak_text = la_speak_text($assistant_raw);

    // Update session memory
    $ctx['last_user_message'] = $message;
    $ctx['last_assistant_raw'] = $assistant_raw;
    $ctx['last_mode'] = $assistant_mode;
    $ctx['last_subject'] = $subject;
    $ctx['updated_at'] = date('c');
    if (preg_match('/teach me\s+(.+)/i', $message, $tm)) {
        $ctx['last_topic'] = trim($tm[1]);
    } elseif (mb_strlen($message) < 200) {
        $ctx['last_topic'] = $message;
    }

    echo json_encode([
        'success'    => true,
        'response'   => $assistant_raw,
        'structured' => $structured,
        'speak_text' => $speak_text,
        'meta'       => [
            'assistant_mode' => $assistant_mode,
            'subject'        => $subject,
            'subject_label'  => $subject_label,
        ],
    ]);
} catch (Exception $e) {
    error_log('learning_assistant: ' . $e->getMessage());
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error'   => 'The learning assistant could not complete your request. Please try again.',
    ]);
}
