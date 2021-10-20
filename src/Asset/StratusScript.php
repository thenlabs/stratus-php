<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Asset;

use ThenLabs\ComposedViews\Asset\Script;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class StratusScript extends Script
{
    use PageTrait;

    /**
     * @return string
     */
    private function getJavaScriptCode(): string
    {
        return <<<JAVASCRIPT
            "use strict";

            class StratusApp {

                constructor(controller, token) {
                    this.controller = controller;
                    this.token = token;
                    this.classes = {};
                    this.components = {};
                    this.debug = false;
                    this.rootElement = document;
                    this.bus = null;
                }

                getClass(id) {
                    return this.classes[id];
                }

                addClass(id, classInstance) {
                    this.classes[id] = classInstance;
                }

                getComponent(id) {
                    return this.components[id];
                }

                addComponent(component) {
                    this.components[component.id] = component;
                }

                setBus(bus) {
                    this.bus = bus;
                }

                getBus() {
                    return this.bus;
                }

                getComponentData() {
                    const componentData = {};

                    for (let componentId in this.components) {
                        let component = this.components[componentId];
                        let criticalData = component.getCriticalData();

                        if (Object.keys(criticalData).length) {
                            componentData[componentId] = criticalData;
                        }
                    }

                    return componentData;
                }

                dispatch(eventName, eventData = {}, capture = false) {
                    this.bus.dispatch(eventName, eventData, capture);
                }
            }
        JAVASCRIPT;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        $source = $this->getJavaScriptCode();

        if (! $this->page->isDebug()) {
            $source = $this->compressJavaScript($source);
        }

        return $source;
    }
}
