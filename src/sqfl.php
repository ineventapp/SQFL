<?php

namespace SQFL;

use RuntimeException;

class Parser {

  private ?string $expression = null;
  private array $tokens = [];

  public function __construct($expression) {
    $this->setExpression($expression);
  }

  private static function tokenize($expression) {
    $expression = trim($expression);
    $stack = [];
    $tokens = [];

    // Check if root is containerized, if not, containerize it
    $expression = "($expression)";

    $index = 0;

    foreach (mb_str_split($expression) as $char) {
      if ($char == "(") {
        array_push($stack, $index);
      } else if ($char == ")") {
        $sindex = array_pop($stack);
        $tokens[] = [
          "start" => $sindex,
          "end" => $index,
          "ref" => "token:" . count($tokens),
          "exp" => mb_substr($expression, $sindex + 1, $index - $sindex - 1)
        ];
      }
      $index++;
    }

    for ($i = 0; $i < count($tokens); $i++) {
      foreach ($tokens as &$ntoken) {
        $token = $tokens[$i];
        if ($token["ref"] == $ntoken["ref"]) continue;
        $ntoken["exp"] = str_replace($token["exp"], '${' . $token["ref"] . '}', $ntoken["exp"]);
      }
    }

    echo json_encode([$expression, $tokens], JSON_PRETTY_PRINT);
  }

  /**
   * Validates expression
   * @param String $expression The SQFL expression
   * @return boolean $isValid Is SQFL expression valid?
   */
  public static function validateExpression($expression) {
    $tokens = Parser::tokenize($expression);
    return true;
  }
  
  /**
   * Returns Parser stored expression
   * @return String $expression The SQFL expression
   */
  public function getExpression() {
    return $this->expression;
  }

  /**
   * Sets expression for Parser
   * @param String $expression The SQFL expression
   * @return void
   * @throws \RuntimeException if expression is invalid
   */
  public function setExpression($expression) {
    $isValid = $this::validateExpression($expression);
    if (!$isValid) {
      throw new \RuntimeException("SQFL expression '$expression' is invalid");
    }
    $this->expression = $expression;
  }
}

$parser = new Parser("name is not 'mauricio' and (status is 'registration' and ((role is 'CEO') or (role is 'CTO'))) or (status is 'invite' and enrollmentDate is '2022-10-10')");


?>