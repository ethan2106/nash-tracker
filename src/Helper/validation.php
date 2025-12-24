<?php

/**
 * Input Validation Helper - Validation et sanitization renforcées.
 *
 * Fonctions de validation pour protéger contre XSS, injection SQL, et autres attaques.
 * Double validation côté client (JavaScript) et serveur (PHP).
 */

/**
 * Sanitize string pour affichage HTML (protection XSS).
 *
 * @param string $input Input utilisateur
 * @return string Input nettoyé
 */
function sanitizeHtml(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Valide et sanitize un email.
 *
 * @param string $email Email à valider
 * @return string|false Email valide ou false
 */
function validateEmail(string $email)
{
    $email = trim($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        return $email;
    }

    return false;
}

/**
 * Valide un nombre (entier ou décimal).
 *
 * @param mixed $input Valeur à valider
 * @param float|null $min Valeur minimale
 * @param float|null $max Valeur maximale
 * @return float|false Nombre valide ou false
 */
function validateNumber($input, ?float $min = null, ?float $max = null)
{
    if (!is_numeric($input))
    {
        return false;
    }

    $number = (float)$input;

    if ($min !== null && $number < $min)
    {
        return false;
    }

    if ($max !== null && $number > $max)
    {
        return false;
    }

    return $number;
}

/**
 * Valide un entier.
 *
 * @param mixed $input Valeur à valider
 * @param int|null $min Valeur minimale
 * @param int|null $max Valeur maximale
 * @return int|false Entier valide ou false
 */
function validateInt($input, ?int $min = null, ?int $max = null)
{
    $number = filter_var($input, FILTER_VALIDATE_INT);

    if ($number === false)
    {
        return false;
    }

    if ($min !== null && $number < $min)
    {
        return false;
    }

    if ($max !== null && $number > $max)
    {
        return false;
    }

    return $number;
}

/**
 * Valide une chaîne (longueur, pattern).
 *
 * @param string $input Chaîne à valider
 * @param int $minLength Longueur minimale
 * @param int $maxLength Longueur maximale
 * @param string|null $pattern Regex pattern optionnel
 * @return string|false Chaîne valide ou false
 */
function validateString(string $input, int $minLength = 1, int $maxLength = 255, ?string $pattern = null)
{
    $input = trim($input);
    $length = mb_strlen($input);

    if ($length < $minLength || $length > $maxLength)
    {
        return false;
    }

    if ($pattern !== null && !preg_match($pattern, $input))
    {
        return false;
    }

    return $input;
}

/**
 * Valide un mot de passe (force minimale).
 *
 * @param string $password Mot de passe à valider
 * @param int $minLength Longueur minimale (défaut: 8)
 * @param bool $requireSpecialChars Nécessite caractères spéciaux
 * @return array ['valid' => bool, 'message' => string]
 */
function validatePassword(string $password, int $minLength = 8, bool $requireSpecialChars = true): array
{
    if (strlen($password) < $minLength)
    {
        return ['valid' => false, 'message' => "Le mot de passe doit contenir au moins {$minLength} caractères"];
    }

    if (!preg_match('/[A-Z]/', $password))
    {
        return ['valid' => false, 'message' => 'Le mot de passe doit contenir au moins une majuscule'];
    }

    if (!preg_match('/[a-z]/', $password))
    {
        return ['valid' => false, 'message' => 'Le mot de passe doit contenir au moins une minuscule'];
    }

    if (!preg_match('/[0-9]/', $password))
    {
        return ['valid' => false, 'message' => 'Le mot de passe doit contenir au moins un chiffre'];
    }

    if ($requireSpecialChars && !preg_match('/[^A-Za-z0-9]/', $password))
    {
        return ['valid' => false, 'message' => 'Le mot de passe doit contenir au moins un caractère spécial'];
    }

    return ['valid' => true, 'message' => 'Mot de passe valide'];
}

/**
 * Valide une date.
 *
 * @param string $date Date au format Y-m-d
 * @param string|null $minDate Date minimale (Y-m-d)
 * @param string|null $maxDate Date maximale (Y-m-d)
 * @return string|false Date valide ou false
 */
function validateDate(string $date, ?string $minDate = null, ?string $maxDate = null)
{
    $d = DateTime::createFromFormat('Y-m-d', $date);

    if (!$d || $d->format('Y-m-d') !== $date)
    {
        return false;
    }

    if ($minDate !== null)
    {
        $min = DateTime::createFromFormat('Y-m-d', $minDate);
        if ($d < $min)
        {
            return false;
        }
    }

    if ($maxDate !== null)
    {
        $max = DateTime::createFromFormat('Y-m-d', $maxDate);
        if ($d > $max)
        {
            return false;
        }
    }

    return $date;
}

/**
 * Valide une URL.
 *
 * @param string $url URL à valider
 * @param bool $requireHttps Nécessite HTTPS
 * @return string|false URL valide ou false
 */
function validateUrl(string $url, bool $requireHttps = false)
{
    $url = trim($url);
    $url = filter_var($url, FILTER_SANITIZE_URL);

    if (!filter_var($url, FILTER_VALIDATE_URL))
    {
        return false;
    }

    if ($requireHttps && !str_starts_with($url, 'https://'))
    {
        return false;
    }

    return $url;
}

/**
 * Protection CSRF - Génère un token.
 *
 * @return string Token CSRF
 */
function generateCsrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE)
    {
        session_start();
    }

    if (!isset($_SESSION['csrf_token']))
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Protection CSRF - Vérifie un token.
 *
 * @param string $token Token à vérifier
 * @return bool Token valide
 */
function verifyCsrfToken(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE)
    {
        session_start();
    }

    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Valide un tableau de données selon des règles.
 *
 * @param array $data Données à valider
 * @param array $rules Règles de validation ['field' => ['type' => 'email', 'required' => true, ...]]
 * @return array ['valid' => bool, 'errors' => array, 'data' => array]
 */
function validateData(array $data, array $rules): array
{
    $errors = [];
    $validated = [];

    foreach ($rules as $field => $rule)
    {
        $value = $data[$field] ?? null;

        // Champ requis
        if (($rule['required'] ?? false) && empty($value))
        {
            $errors[$field] = "Le champ {$field} est obligatoire";

            continue;
        }

        // Si pas requis et vide, skip
        if (empty($value))
        {
            $validated[$field] = null;

            continue;
        }

        // Validation selon le type
        $type = $rule['type'] ?? 'string';

        switch ($type)
        {
            case 'email':
                $result = validateEmail($value);
                if ($result === false)
                {
                    $errors[$field] = 'Email invalide';
                } else
                {
                    $validated[$field] = $result;
                }

                break;

            case 'int':
                $result = validateInt($value, $rule['min'] ?? null, $rule['max'] ?? null);
                if ($result === false)
                {
                    $errors[$field] = 'Nombre entier invalide';
                } else
                {
                    $validated[$field] = $result;
                }

                break;

            case 'float':
                $result = validateNumber($value, $rule['min'] ?? null, $rule['max'] ?? null);
                if ($result === false)
                {
                    $errors[$field] = 'Nombre invalide';
                } else
                {
                    $validated[$field] = $result;
                }

                break;

            case 'string':
                $result = validateString($value, $rule['minLength'] ?? 1, $rule['maxLength'] ?? 255, $rule['pattern'] ?? null);
                if ($result === false)
                {
                    $errors[$field] = 'Texte invalide';
                } else
                {
                    $validated[$field] = sanitizeHtml($result);
                }

                break;

            case 'date':
                $result = validateDate($value, $rule['minDate'] ?? null, $rule['maxDate'] ?? null);
                if ($result === false)
                {
                    $errors[$field] = 'Date invalide';
                } else
                {
                    $validated[$field] = $result;
                }

                break;

            case 'url':
                $result = validateUrl($value, $rule['requireHttps'] ?? false);
                if ($result === false)
                {
                    $errors[$field] = 'URL invalide';
                } else
                {
                    $validated[$field] = $result;
                }

                break;

            default:
                $validated[$field] = sanitizeHtml($value);
        }
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $validated,
    ];
}

/**
 * Échappe pour JavaScript (prévention XSS dans inline scripts).
 *
 * @param string $input Input à échapper
 * @return string Input échappé pour JS
 */
function escapeJs(string $input): string
{
    return json_encode($input, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
