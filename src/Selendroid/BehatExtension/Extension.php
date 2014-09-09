<?php
namespace Selendroid\BehatExtension;

use Behat\Behat\Extension\ExtensionInterface;
use Behat\MinkExtension\Compiler;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class Extension implements ExtensionInterface
{

    /**
     * Loads a specific configuration.
     *
     * @param array $config Extension configuration hash (from behat.yml)
     * @param ContainerBuilder $container ContainerBuilder instance
     *
     * @throws \RuntimeException
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/services'));
//        $loader->load('core.xml');

        if (isset($config['selendroid'])) {
            if (!class_exists('Selendroid\\Driver\\SelendroidDriver')) {
                throw new \RuntimeException(
                    'Install SelendroidDriver in order to activate selendroid session.'
                );
            }

            $loader->load('sessions/selendroid.xml');
        }

        $minkParameters = array();
        foreach ($config as $ns => $tlValue) {
            if (!is_array($tlValue)) {
                $minkParameters[$ns] = $tlValue;
            } else {
                foreach ($tlValue as $name => $value) {
                    if ('guzzle_parameters' === $name) {
                        $value['redirect.disable'] = true;
                    }

                    $container->setParameter("behat.mink.$ns.$name", $value);
                }
            }
        }
        $container->setParameter('behat.mink.parameters', $minkParameters);

        if (isset($config['base_url'])) {
            $container->setParameter('behat.mink.base_url', $config['base_url']);
        }
        $container->setParameter('behat.mink.default_session', $config['default_session']);
        $container->setParameter('behat.mink.javascript_session', $config['javascript_session']);
        $container->setParameter('behat.mink.browser_name', $config['browser_name']);

        $minkReflection = new \ReflectionClass('Behat\Mink\Mink');
        $minkLibPath    = realpath(dirname($minkReflection->getFilename()) . '/../../../');
        $container->setParameter('mink.paths.lib', $minkLibPath);

        if ($config['show_auto']) {
            $loader->load('failure_show_listener.xml');
        }
    }

    /**
     * Setups configuration for current extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function getConfig(ArrayNodeDefinition $builder)
    {
        $config = $this->loadEnvironmentConfiguration();

        $builder->
            children()->
                scalarNode('mink_loader')->
                    defaultValue(isset($config['mink_loader']) ? $config['mink_loader'] : null)->
                end()->
                scalarNode('base_url')->
                    defaultValue(isset($config['base_url']) ? $config['base_url'] : null)->
                end()->
                scalarNode('files_path')->
                    defaultValue(isset($config['files_path']) ? $config['files_path'] : null)->
                end()->
                booleanNode('show_auto')->
                    defaultValue(isset($config['show_auto']) ? 'true' === $config['show_auto'] : false)->
                end()->
                scalarNode('show_cmd')->
                    defaultValue(isset($config['show_cmd']) ? $config['show_cmd'] : null)->
                end()->
                scalarNode('show_tmp_dir')->
                    defaultValue(isset($config['show_tmp_dir']) ? $config['show_tmp_dir'] : sys_get_temp_dir())->
                end()->
                scalarNode('default_session')->
                    defaultValue(isset($config['default_session']) ? $config['default_session'] : 'goutte')->
                end()->
                scalarNode('javascript_session')->
                    defaultValue(isset($config['javascript_session']) ? $config['javascript_session'] : 'selendroid')->
                end()->
                scalarNode('browser_name')->
                    defaultValue(isset($config['browser_name']) ? $config['browser_name'] : 'android')->
                end()->
                arrayNode('selendroid')->
                    children()->
                        scalarNode('browser')->
                            defaultValue(isset($config['selendroid']['browser']) ? $config['selendroid']['browser'] : '%behat.mink.browser_name%')->
                        end()->
                        arrayNode('capabilities')->
                            children()->
                                scalarNode('browserName')->
                                    defaultValue(isset($config['selendroid']['capabilities']['browserName']) ? $config['selendroid']['capabilities']['browserName'] : 'android')->
                                end()->
                                scalarNode('emulator')->
                                    defaultValue(isset($config['selendroid']['capabilities']['emulator']) ? $config['selendroid']['capabilities']['emulator'] : "True")->
                                end()->
                                scalarNode('platformVersion')->
                                    defaultValue(isset($config['selendroid']['capabilities']['platformVersion']) ? $config['selendroid']['capabilities']['platformVersion'] : "19")->
                                end()->
                                scalarNode('locale')->
                                    defaultValue(isset($config['selendroid']['capabilities']['locale']) ? $config['selendroid']['capabilities']['locale'] : "en_US")->
                                end()->
                                scalarNode('screenSize')->
                                    defaultValue(isset($config['selendroid']['capabilities']['screenSize']) ? $config['selendroid']['capabilities']['screenSize'] : "720x1280")->
                                end()->
                            end()->
                        end()->
                        scalarNode('wd_host')->
                            defaultValue(isset($config['selendroid']['wd_host']) ? $config['selendroid']['wd_host'] : 'http://localhost:4444/wd/hub')->
                        end()->
                    end()->
                end()->
            end()->
        end();
    }

    /**
     * Returns compiler passes used by this extension.
     *
     * @return array
     */
    public function getCompilerPasses()
    {
        return array(
            new Compiler\SelectorsPass(),
            new Compiler\SessionsPass(),
        );
    }

    /**
     * @return array
     */
    protected function loadEnvironmentConfiguration()
    {
        $config = array();
        if ($envConfig = getenv('MINK_EXTENSION_PARAMS')) {
            parse_str($envConfig, $config);
        }

        return $config;
    }
}