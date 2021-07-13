<?php

declare(strict_types=1);

namespace Doctrine\ORM\Tools;

use Doctrine\Deprecations\Deprecation;
use Doctrine\ORM\EntityRepository;

use function array_keys;
use function array_values;
use function chmod;
use function dirname;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function str_replace;
use function strlen;
use function strrpos;
use function substr;

use const DIRECTORY_SEPARATOR;

/**
 * Class to generate entity repository classes
 *
 * @deprecated 2.7 This class is being removed from the ORM and won't have any replacement
 *
 * @link    www.doctrine-project.org
 */
class EntityRepositoryGenerator
{
    /** @psalm-var class-string */
    private $repositoryName;

    /** @var string */
    protected static $_template =
    '<?php

<namespace>

/**
 * <className>
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class <className> extends <repositoryName>
{
}
';

    public function __construct()
    {
        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/orm/issues/8458',
            '%s is deprecated and will be removed in Doctrine ORM 3.0',
            self::class
        );
    }

    /**
     * @param string $fullClassName
     *
     * @return string
     */
    public function generateEntityRepositoryClass($fullClassName)
    {
        $variables = [
            '<namespace>'       => $this->generateEntityRepositoryNamespace($fullClassName),
            '<repositoryName>'  => $this->generateEntityRepositoryName($fullClassName),
            '<className>'       => $this->generateClassName($fullClassName),
        ];

        return str_replace(array_keys($variables), array_values($variables), self::$_template);
    }

    /**
     * Generates the namespace, if class do not have namespace, return empty string instead.
     *
     * @psalm-param class-string $fullClassName
     */
    private function getClassNamespace(string $fullClassName): string
    {
        return substr($fullClassName, 0, (int) strrpos($fullClassName, '\\'));
    }

    /**
     * Generates the class name
     *
     * @psalm-param class-string $fullClassName
     */
    private function generateClassName(string $fullClassName): string
    {
        $namespace = $this->getClassNamespace($fullClassName);

        $className = $fullClassName;

        if ($namespace) {
            $className = substr($fullClassName, strrpos($fullClassName, '\\') + 1, strlen($fullClassName));
        }

        return $className;
    }

    /**
     * Generates the namespace statement, if class do not have namespace, return empty string instead.
     *
     * @psalm-param class-string $fullClassName The full repository class name.
     */
    private function generateEntityRepositoryNamespace(string $fullClassName): string
    {
        $namespace = $this->getClassNamespace($fullClassName);

        return $namespace ? 'namespace ' . $namespace . ';' : '';
    }

    private function generateEntityRepositoryName(string $fullClassName): string
    {
        $namespace = $this->getClassNamespace($fullClassName);

        $repositoryName = $this->repositoryName ?: EntityRepository::class;

        if ($namespace && $repositoryName[0] !== '\\') {
            $repositoryName = '\\' . $repositoryName;
        }

        return $repositoryName;
    }

    /**
     * @param string $fullClassName
     * @param string $outputDirectory
     *
     * @return void
     */
    public function writeEntityRepositoryClass($fullClassName, $outputDirectory)
    {
        $code = $this->generateEntityRepositoryClass($fullClassName);

        $path = $outputDirectory . DIRECTORY_SEPARATOR
              . str_replace('\\', DIRECTORY_SEPARATOR, $fullClassName) . '.php';
        $dir  = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if (! file_exists($path)) {
            file_put_contents($path, $code);
            chmod($path, 0664);
        }
    }

    /**
     * @param string $repositoryName
     *
     * @return static
     */
    public function setDefaultRepositoryName($repositoryName)
    {
        $this->repositoryName = $repositoryName;

        return $this;
    }
}
