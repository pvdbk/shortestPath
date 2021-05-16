<?php

/* The class implemented here provides objects which validate/reject jsons according to a schema.
 *
 * Here are the public methods of the class :
 *   - __construc(mixed $schema)
 *   - static testSchema(mixed $schema): ?array
 *   - test(mixed $data, bool $decodeString = true): ?array
 *
 * Here is a snippet which should explain what do the three. $schema and $data are anything.
 *
 * $schemaTest = Validator::testSchema($schema));
 * if (is_null($schemaTest)) {
 *   echo '$schema is a valid schema.';
 *   $v = new Validator($schema);
 *   $test = $v->test($data);
 *   if (is_null($test)) {
 *     echo '$data matches $schema.';
 *   } else {
 *     echo '$data doesn't match $schema. Here are some details about :' . PHP_EOL;
 *     var_export($test);
 *   }
 * } else {
 *   echo '$schema is not a valid schema. Here are some details about :' . PHP_EOL;
 *   var_export($schemaTest);
 *   echo 'Now, let's raise an exception.'
 *   new Validator($schema);
 * }
 *
 * So the methods "test" and "testSchema" returns null if their conclusion is positive.
 * Otherwise, they return an array with some details.
 * Two question remain :
 *   - What is a valid schema ?
 *   - Given a valid schema, which data match with it ?
 *
 * The schemas handled here look like other schemas which describe types of data,
 * but are not the same. They still concern the jsons and are jsons.
 *
 * So, to make the two methods return null, you have to give them a json.
 * Concretely, this would be a JSON string or a json_decode($json), where $json is any JSON string.
 * The two methods parse any string they receive, unless $decodeString is false.
 *
 * Talking about jsons, one of them is null and each other belong to one of this type :
 *   - boolean
 *   - number
 *   - string
 *   - array
 *   - object
 *
 * The five name will refer to jsons in what follows (unless stated otherwise)
 * and the verb "recognize" will be used in a strict and exhaustive way.
 *
 * Here are some types of schemas :
 *   - null : Recognize (all) the jsons
 *   - {"const": *json*} : Recognizes (only) *json*
 *   - {"enum": *array*} : Recognizes (all) the jsons contained by *array* (and only them)
 *   - {"type": "boolean"} : Recognizes true and false
 *   - {"type": "array", "items": *schema*} : Recognize every array whose items are recognized by *schema*
 *
 * As we can see, when "type" is "array", there can also be "items". The json expected for this key
 * (in this type of schemas) is any schema, and the default value is null.
 *
 * Generally, The schemas with a "type" key accepts different other keys. Those key depend on the "type".
 *
 * There are three more important types of schemas we need to know. So we will talk about them
 * in what follows.
 *
 * When a schema is an array, it contains schemas. Then it recognizes every schema
 * recognized by one of those which it contains. So [] doesn't recognize anything and
 * [{"const": "Hello"}, {"const": "Hi"}] equates to {"enum": ["Hello", "Hi"]}.
 *
 * To describe strictly the schemas of this type, we could use this schema :
 * {
 *   "type": "array"
 *   "items": *motherSchema*
 * }
 *
 * Here, *schemasMother* refers to a schema wich recognizes the schemas, and this one should be a part of this.
 * Without a way to refer to a portion of schema, the complete writing of this schema (or schemasMother)
 * is impossible or has no end.
 *
 * In this way, the second missing type of schemas comes itself. We will introduce it with an example.
 * So, let's define what is a "thing" : It's a boolean or an array of "things".
 * This schema gives the same definition :
 * [
 *   {"type": "boolean"},
 *   {
 *     "type": "array",
 *     "items": {"ref": "/"}
 *   }
 * ]
 *
 * So here is the type of schema in question : {"ref": *ref*}
 * {"ref": "/"} and {"ref": ""} refers always to the entire schema.
 * So we can understand how the schema sticks to the definition.
 *
 * To use *ref*, we have to know how each json in a schema is referred.
 * To enlight this, let's add some references at the example :
 * [                    // /
 *   {                  // /0
 *     "type": "boolean"   // /0/type
 *   },
 *   {                  // /1
 *     "type": "array"  // /1/type
 *     "items": {       // /1/items
 *       "ref": "../.." // /1/items/ref
 *     }
 *   }
 * ]
 *
 * We assume that the meaning of ".." is sufficiently obvious (most files systems use the same writing).
 *
 * To resolve relative references (they always should begin with "../"),
 * the considered start is the {"ref": *ref*} in question.
 * So this last schema equates to the previous one.
 *
 * Besides "..", you can use "." to write *ref* even if it's quite useless.
 * You can also add a "/" at then end of a reference, it doesn't change anything,
 * unless you add more than one. ("[...]//[---]" equates to "/[---]")
 *
 * These strings are accepted as schema keys : ".", "..", "yip/./yop", ...
 * Nethertheless, such uses are discouraged. Obviously, they can lead
 * to unexpected behaviours when there is a {"ref": *ref*}.
 *
 * Let's consider these schemas :
 *   - {"ref": "."}
 *   - [{"const": "Yop"}, {"ref": ".."}]
*
 * Such schemas are rejected by the "testSchema" method because they can't be handled.
 *
 * Here is the last type we would like to approach : {"type": "schema", ...}
 * In those schemas, you can declare required keys and what can be the associated values,
 * adding this field:
 * "required": {"key1": *schema1*, "key2": *schema2*, ...}    (default value: {})
 * You can do the same with optional keys :
 * "optional": {"key1": *schema1*, "key2": *schema2*, ...}    (default value: {})
 * And if you want other keys to be accepted according to a schema, you can add this field:
 * "other": *schema*    (default value: [])
 *
 * Here is an example :
 * {
 *   "type": "object"                               // Recognizes objects
 *   "required": {                                  // wich have at leat these two keys: "salutation" and "smiling"
 *     "salutation": {"enum": {"Hello", "Hi"}},     // "salutation" can have only two values.
 *     "isSmiling": {"type": "boolean"}                // So can "isSmiling".
 *   }
 *   "optional": {"name": {"type": "string"}},      // If there is a "name", it must be a string.
 *   "other": null                                  // Any other (string) key is accepted with any (json) value.
 * }
 *
 * Now, we know enough the schemas to write a motherSchemas which recognizes the evoked schema.
 * Unfortunately, such a schema would be to permissive.
 *
 * Actually there's a motherSchemas, stored in a json file which has the same name. It says nearly everything about
 * what can be a schema. We only have to take in count three additional (and very natural) conditions,
 * to determine which jsons are schemas worthy of that name :
 *   - They can't be unintelligible (in the way we've seen previously).
 *   - "required" and "optional" can't have common keys in the same {"type": "object", ...}.
 *   - *ref* must be resolvable.
 *
 * When some keys are optional in a type of schema, the default value is generally the most permissive.
 * In this way, in {"type": "array", ...}, the default value for "items" is null.
 * This rule has only few exceptions, already seen :
 *   - "nullabe" in {"type": *string*, ...}: The default value is the least permissive.
 *   - "other" in {"type": "object", ...}: Likewise
 *   - "optional" and "required" in {"type": "object", ...}: We can't talk about a most permissive value.
 *
 * There's no much to add about the schemas, except the objects with a "type" key.
 * They also accept an optional key : "nullable".
 * It takes a boolean and the default value is false. When it's true, null is recognized in addition.
 * The other keys admitted and the jsons they accept depend of the value of "type" and can be seen in motherSchemas.
 *
 * To get more informations about what is recognized by a {"type": *type*, ...}, you can wait for a better documentation,
 * or look at the implementation of buildStringTypeTest for {"type": "string", ...}, and so on.
 */

namespace App;

class Validator {
    use \Dependencies\Injection;
    use \Utils\Jsons;
    private static string $exception;
    private static ?\Closure $schemasTester = null;
    private static bool $initializing = false;
    private null|bool|array|object $schema;
    private ?array $contiguousRefs;
    private array $tests;
    private \Closure $builtTest;

    public function __construct(mixed $schema)
    {
        self::init();
        $this->setSchema($schema);
        $this->tests = [];
        $this->contiguousRefs = [];
        $this->prepareSchema($this->schema, '');
        $this->checkRecursiveDefs();
        $this->builtTest = $this->buildTest($this->schema, '');
        $this->checkRefs();
    }

    private function setSchema(mixed $schema)
    {
        extract(self::parseOrCheck($schema));
        if (!self::$initializing) {
            $schemaError = $success
                ? (self::$schemasTester)($result)
                : $result;
            if (!is_null($schemaError)) {
                throw new (self::$exception)(
                    'Invalid schema',
                    $schemaError,
                    'internalError'
                );
            }
        }
        $this->schema = $result;
    }

    private function checkRefs()
    {
        foreach ($this->tests as $ref => $test)
        {
            if (is_null($test)) {
                throw new (self::$exception)(
                    '"' . $ref . '" is not a a schema reference.',
                    $this->schema,
                    'internalError'
                );
            }
        }
    }

    public static function testSchema(mixed $schema): ?array
    {
        try {
            new self($schema);
            $ret = null;
        } catch(\Exception $e) {
            $getDetails = 'getDetails';
            $ret = [
                'message' => $e->getMessage(),
                'details' => $e->$getDetails()
            ];
        }
        return $ret;
    }

    public function test(mixed $toTest, bool $decodeString = true): null|string|array
    {
        extract(self::parseOrCheck($toTest, $decodeString));
        return $success
            ? ($this->builtTest)($result)
            : $result;
    }

    private static function init()
    {
        if (self::$schemasTester === null && !self::$initializing) {
            self::$initializing = true;
            self::$exception = self::getDep('apiException');
            self::$schemasTester = (new self(
                file_get_contents(__DIR__ . '/../../jsons/schemasMother.json')
            ))->builtTest;
            self::$initializing = false;
        }
    }

    private static function parseOrCheck(mixed $json, $decodeString = true): array
    {
        try {
            $ret = ($decodeString && is_string($json))
                ? [
                    'success' => true,
                    'result' => json_decode($json, false, 512, JSON_THROW_ON_ERROR)
                ]
                : match(self::isJson($json)) {
                    null => [
                        'success' => false,
                        'result' => 'The given value has circular references.'
                    ],
                    false => [
                        'success' => false,
                        'result' => 'The given value is neither a return of json_decode nor a ' . ($decodeString ? 'JSON ' : '') . 'string.',
                    ],
                    true => [
                        'success' => true,
                        'result' => $json
                    ]
                };
        } catch(\Exception) {
            $ret = [
                'success' => false,
                'result' => [
                    'message' => 'The given string is not a JSON string.',
                    'given' => $json
                ]
            ];
        }
        return $ret;
    }

    private function getSchemaType(null|array|object $schema): string {
        if ($schema === null) {
            $ret = 'null';
        } elseif (is_array($schema)) {
            $ret = 'array';
        } else {
            $toArray = get_object_vars($schema);
            $types = ['ref', 'enum', 'const'];
            $i = self::firstKey(
                $types,
                function(string $type) use ($toArray): bool {
                    return key_exists($type, $toArray);
                }
            );
            $ret = is_null($i) ? $toArray['type'] . 'Type' : $types[$i];
        }
        return $ret;
    }

    private function prepareSchema(null|array|object $schema, string $path)
    {
        $type = $this->getSchemaType($schema);
        if (str_ends_with($type, 'Type') && !(isset($schema->nullable))) {
            $schema->nullable = false;
        }
        if (in_array($type, ['array', 'ref', 'arrayType', 'objectType'])) {
            $this->{'prepare'. ucfirst($type) . 'Schema'}($schema, $path);
        }
    }

    private function prepareArraySchema(array &$schema, string $path) {
        $this->contiguousRefs[$path] = [];
        foreach ($schema as $i => &$subSchema) {
            $itemPath = $path . '/'. $i;
            $this->prepareSchema($subSchema, $itemPath);
            $this->contiguousRefs[$path][] = $itemPath;
        }
    }

    private function prepareRefSchema(object $schema, string $path) {
        $resolveDoubleDots = function($handledPath) use($schema, $path) {
            $exp = explode('/', $handledPath);
            if (count($exp) <= 1) {
                throw new (self::$exception)(
                    'A reference is unresolvable.',
                    $schema->ref,
                    'internalError'
                );
            }
            array_pop($exp);
            return implode('/', $exp);
        };
        $resolveRef = function(?string $ref, string $path) use(&$resolveRef, $resolveDoubleDots): string {
            if (is_null($ref)) {
                $ret = $path;
            } else {
                $exp = explode('/', $ref, 2);
                if (count($exp) !== 2) {
                    $exp = [$ref, null];
                } elseif ($exp[1] == '') {
                    $exp = [$exp[0], null];
                }
                $ret = match($exp[0]) {
                        '' => $resolveRef($exp[1], ''),
                        '.' => $resolveRef($exp[1], $path),
                        '..' => $resolveRef($exp[1], $resolveDoubleDots($path)),
                        default => $resolveRef($exp[1], $path . '/' . $exp[0])
                    };
            }
            return $ret;
        };
        $schema->ref = $resolveRef($schema->ref, $path);
        $this->tests[$schema->ref] = null;
        $this->contiguousRefs[$path] = [$schema->ref];
    }

    private function prepareArrayTypeSchema(object $schema, string $path) {
        if (!(isset($schema->items))) {
            $schema->items = null;
        } else {
            $this->prepareSchema($schema->items, $path . '/items');
        }
    }

    private function prepareObjectTypeSchema(object $schema, string $path) {
        foreach (['required', 'optional'] as $key) {
            if (!(isset($schema->$key))) {
                $schema->$key = (object) [];
            } else {
                $pathPrefix = $path . '/' . $key . '/';
                foreach (array_keys(get_object_vars($schema->$key)) as $subKey) {
                    $this->prepareSchema($schema->$key->$subKey, $pathPrefix . $subKey);
                }
            }
        }
        if (!(isset($schema->other))) {
            $schema->other = [];
        } else {
            $this->prepareSchema($schema->other, $path . '/other');
        }
    }

    private function checkRecursiveDefs()
    {
        $scan = function(string $ref) use(&$scan) {
            $refs = $this->contiguousRefs[$ref];
            if (is_null($refs)) {
                throw new (self::$exception)(
                    'A recursive definition is unresolvable.',
                    [
                        'schema' => $this->schema,
                        'involved' => $ref
                    ],
                    'internalError'
                );
            }
            $this->contiguousRefs[$ref] = null;
            foreach ($refs as $r) {
                if (key_exists($r, $this->contiguousRefs)) {
                    $scan($r);
                }
            }
            unset($this->contiguousRefs[$ref]);
        };
        $ref = array_key_first($this->contiguousRefs);
        while (!is_null($ref)) {
            $scan($ref);
            $ref = array_key_first($this->contiguousRefs);
        }
        unset($this->contiguousRefs);
    }

    private function buildTest(null|array|object $schema, string $path): \Closure
    {
        $test = $this->{'build' . ucfirst($this->getSchemaType($schema)) . 'Test'}($schema, $path);
        $schemaType = $this->getSchemaType($schema);
        if (preg_match('`^(.*)Type$`', $schemaType, $matches) === 1) {
            $type = $matches[1];
            $testType = \Closure::fromCallable(match($type) {
                'boolean' => 'is_bool',
                'string' => 'is_string',
                'array' => 'is_array',
                'object' => 'is_object',
                'number' => function($toTest) {
                    return is_int($toTest) || is_float($toTest);
                }
            });
            $specimen = (in_array($type[0], ['a', 'o']) ? 'an ' : 'a ') . $type;
            if($schema->nullable) {
                $errorType = 'The given value is neither null nor ' . $specimen . '.';
                $test = function(mixed $toTest) use($test, $testType, $errorType): null|string|array {
                    return is_null($toTest)
                        ? null
                        : ($testType($toTest) ? $test($toTest) : [
                            'message' => $errorType,
                            'given' => $toTest
                        ]);
                };
            } else {
                $errorType = 'The given value is not ' . $specimen . '.';
                $test = function(mixed $toTest) use($test, $testType, $errorType): null|string|array {
                    return $testType($toTest) ? $test($toTest) : [
                        'message' => $errorType,
                        'given' => $toTest
                    ];
                };
            }
        }
        if (key_exists($path, $this->tests)) {
            $this->tests[$path] = $test;
        }
        return $test;
    }

    private function buildNullTest(): \Closure
    {
        return function () {
            return null;
        };
    }

    private function buildArrayTest(array $schema, string $path): \Closure
    {
        $tests = [];
        foreach ($schema as $i => $subSchema)
        {
            $tests[] = $this->buildTest($subSchema, $path . '/' . $i);
        }
        return count($schema) === 0
            ? function() {
                return 'There mustn\'t be any value.';
            }
            : function(mixed $toTest) use($tests): ?array {
                $errors = [];
                return self::every($tests, function(\Closure $test) use($toTest, &$errors) {
                    $error = $test($toTest);
                    $errors[] = $error;
                    return !is_null($error);
                }) ? [
                    'message' => 'Every test has failed.',
                    'errors' => $errors
                ] : null;
            };
    }

    private function buildRefTest(object $schema): \Closure
    {
        $ref = $schema->ref;
        return function(mixed $toTest) use($ref): null|string|array {
            return ($this->tests[$ref])($toTest);
        };
    }

    private function buildEnumTest(object $schema): \Closure
    {
        $enum = $schema->enum;
        return function(mixed $toTest) use($enum): ?array {
            return self::every($enum, function(mixed $item) use($toTest):bool {
                return !self::jsonEquality($item, $toTest);
            })
                ? [
                    'message' => 'The given value is not in the enum.',
                    'given' => $toTest,
                    'enum' => $enum,
                ]
                : null;
        };
    }

    private function buildConstTest(object $schema): \Closure
    {
        $const = $schema->const;
        return function(mixed $toTest) use($const): ?array {
            return self::jsonEquality($const, $toTest)
                ? null
                : [
                    'message' => 'The given value is not the expected one.',
                    'given' => $toTest,
                    'expected' => $const,
                ];
        };
    }

    private function buildBooleanTypeTest(): \Closure
    {
        return function() {
            return null;
        };
    }

    private function buildNumberTypeTest(): \Closure
    {
        return function() {
            return null;
        };
    }

    private function buildStringTypeTest(): \Closure
    {
        return function() {
            return null;
        };
    }

    private function buildArrayTypeTest(object $schema, string $path): \Closure
    {
        $test = $this->buildTest($schema->items, $path . '/items');
        return function(mixed $toTest) use($test): null|string|array {
            $error = null;
            for ($i = 0; is_null($error) && $i < count($toTest); $i++) {
                $error = $test($toTest[$i]);
            }
            return is_null($error) ? null : [
                'message' => 'An item doesn\'t match.',
                'index' => $i-1,
                'error' => $error
            ];
        };
    }

    private function buildObjectTypeTest(object $schema, string $path): \Closure
    {
        $requiredKeys = array_keys(get_object_vars($schema->required));
        $optionalKeys = array_keys(get_object_vars($schema->optional));
        if (!self::every(
            $requiredKeys,
            function(string $key) use($optionalKeys): bool {
                return !in_array($key, $optionalKeys);
            }
        )) {
            throw new (self::$exception)(
                'A required key can\'t be optional.',
                $schema,
                'internalError'
            );
        };
        $forRequired = [];
        foreach ($schema->required as $key => $subSchema) {
            $forRequired[$key] = $this->buildTest($subSchema, $path . '/required/' . $key);
        }
        $forOptional = [];
        foreach ($schema->optional as $key => $subSchema) {
            $forOptional[$key] = $this->buildTest($subSchema, $path . '/optional/' . $key);
        }
        $forOther = $this->buildTest($schema->other, $path . '/other');
        return function(mixed $toTest) use($forRequired, $forOptional, $forOther): null|string|array {
            $error = null;
            $requiredFound = 0;
            $keys = array_keys(get_object_vars($toTest));
            for ($i = 0; is_null($error) && $i < count($keys); $i++) {
                $key = $keys[$i];
                if (key_exists($key, $forRequired)) {
                    $test = $forRequired[$key];
                    $requiredFound++;
                } elseif (key_exists($key, $forOptional)) {
                    $test = $forOptional[$key];
                } else {
                    $test = $forOther;
                }
                $error = $test($toTest->$key);
            }
            return is_null($error)
                ? ($requiredFound === count($forRequired)
                    ? null
                    : [
                        'message' => 'Some required keys are missing.',
                        'missing' => array_filter(
                            array_keys($forRequired),
                            function(string $key) use($keys): bool {
                                return !in_array($key, $keys);
                            }
                        ),
                        'in' => $toTest
                    ]
                )
                : [
                    'message' => 'An item doesn\'t match.',
                    'key' => $key . '',
                    'error' => $error
                ];
        };
    }
}
