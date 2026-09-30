<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Base exception for business-rule violations. Rendered as a flash error + redirect back.
 */
class NitipException extends RuntimeException {}
