<?php
declare(strict_types=1);

namespace ThenLabs\StratusPHP\Bus;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class StreamingBus implements BusInterface
{
    /**
     * {@inheritdoc}
     */
    public function open()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data)
    {
        echo json_encode($data).'%SSS%';

        ob_flush();
        flush();
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        die;
    }

    public static function getJavaScriptClassMembers(): string
    {
        return <<<JAVASCRIPT
            constructor(app) {
                this.app = app;
                this.httpRequests = [];
                this.frontCallsResultsBuffer = {};
            }

            dispatch(eventName, eventData = {}, capture = false) {
                const xhr = this.getNewXMLHttpRequest();
                const componentData = this.app.getComponentData();

                const data = {
                    token: this.app.token,
                    componentData,
                    eventName,
                    eventData,
                    capture,
                };

                this.sendRequest(xhr, data);
            }

            getNewXMLHttpRequest() {
                const xhr = new XMLHttpRequest();
                xhr.lastResponseLen = 0;

                xhr.onprogress = this._onprogress;
                xhr.onreadystatechange = this._onreadystatechange;

                xhr.open('POST', this.app.controller, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                return xhr;
            }

            _onprogress(event) {
                if (! event.currentTarget) return;

                let currentResponse = null;
                let responseBuffer = event.currentTarget.response;

                if (this.lastResponseLen === false) {
                    currentResponse = responseBuffer;
                    this.lastResponseLen = responseBuffer.length;
                } else {
                    currentResponse = responseBuffer.substring(this.lastResponseLen);
                    this.lastResponseLen = responseBuffer.length;
                }

                if ('string' === typeof(currentResponse)) {
                    bus.processMessage(currentResponse, this);
                }
            }

            _onreadystatechange() {
                const xhr = this;

                if (xhr.readyState === XMLHttpRequest.DONE) {
                    bus.httpRequests.splice(
                        bus.httpRequests.indexOf(xhr), 1
                    );
                }
            }

            sendRequest(xhr, data) {
                xhr.data = data;
                xhr.send('stratus_request=' + JSON.stringify(data, this._stringifyReplacer));
                this.httpRequests.push(xhr);
                this.frontCallsResultsBuffer = {};
            }

            _stringifyReplacer(key, value) {
                let result = value;

                if (value instanceof NamedNodeMap) {
                    result = {};

                    for (let attr of value) {
                        let attrName = attr.nodeName;
                        result[attrName] = attr.ownerElement.getAttribute(attrName);
                    }
                }

                return result;
            }

            processMessage(text, xhr) {
                if ('string' !== typeof(text)) {
                    return;
                }

                let lines = text.split('%SSS%');
                for (let id in lines) {
                    let line = lines[id];

                    line = line.trim();

                    if (! line.length) {
                        continue;
                    }

                    let message = JSON.parse(line);

                    if (this.app.debug) {
                        console.log('Message:', message);
                    }

                    if ('object' === typeof message.handler) {
                        let HandlerClass = this.app.classes[message.handler.classId];
                        let handler = HandlerClass[message.handler.method];

                        handler.apply(null, Object.values(message.data));
                    }

                    if ('object' === typeof message.frontCall) {
                        let frontCallResult = eval('(function() {' + message.frontCall.script + '})()');

                        this.frontCallsResultsBuffer[message.frontCall.hash] = frontCallResult ? frontCallResult : '';
                    }

                    if ('boolean' === typeof(message.resend) &&
                        true === message.resend
                    ) {
                        if (this.app.debug) {
                            console.log('The current request should be sent again.');
                        }

                        let data = xhr.data;

                        if ('object' !== typeof data.executedFrontCalls) {
                            data.executedFrontCalls = {};
                        }

                        if ('object' === typeof message.executedFrontCalls) {
                            Object.assign(data.executedFrontCalls, message.executedFrontCalls);
                        }

                        Object.assign(data.executedFrontCalls, this.frontCallsResultsBuffer);

                        data.componentData = this.app.getComponentData();

                        let newXhr = this.getNewXMLHttpRequest();
                        this.sendRequest(newXhr, data);
                    }
                }
            }
        JAVASCRIPT;
    }

    public function getJavaScriptCreateInstanceScript(): string
    {
        return <<<JAVASCRIPT
            window.bus = new Bus(stratusAppInstance);
            stratusAppInstance.setBus(bus);
        JAVASCRIPT;
    }
}
