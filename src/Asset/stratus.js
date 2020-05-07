"use strict";

class StratusApp {

    constructor(controller, token) {
        this.controller = controller;
        this.token = token;
        this.classes = {};
        this.components = {};
        this.buffer = {};
        this.rootElement = document;
        this.httpRequests = [];
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

    dispatch(eventName) {
        const xhr = new XMLHttpRequest();
        xhr.lastResponseLen = 0;

        xhr.onprogress = event => {
            if (! event.currentTarget) return;

            let currentResponse = null;
            let responseBuffer = event.currentTarget.response;

            if (xhr.lastResponseLen === false) {
                currentResponse = responseBuffer;
                xhr.lastResponseLen = responseBuffer.length;
            } else {
                currentResponse = responseBuffer.substring(xhr.lastResponseLen);
                xhr.lastResponseLen = responseBuffer.length;
            }

            if ('string' === typeof(currentResponse)) {
                this.processMessage(currentResponse);
            }
        };

        xhr.onreadystatechange = () => {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                this.httpRequests.splice(
                    this.httpRequests.indexOf(xhr), 1
                );

                if (xhr.status === 200) {
                    this.processMessage(xhr.responseText);
                }
            }
        };

        xhr.open('POST', this.controller, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        const request = {
            token: this.token,
            componentData: this.buffer,
            eventName,
        };

        xhr.send('stratus_message=' + JSON.stringify(request));
        this.httpRequests.push(xhr);
        this.buffer = {};
    }

    processMessage(text) {
        if ('string' !== typeof(text)) {
            return;
        }

        var lines = text.split('%SSS%');
        for (var id in lines) {
            var line = lines[id];

            if (! line.length) {
                continue;
            }

            var message = JSON.parse(line);
            var HandlerClass = this.classes[message.handler.classId];

            HandlerClass[message.handler.method](message.data);
        }
    }
}
