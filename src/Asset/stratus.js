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

    getNewXMLHttpRequest() {
        const xhr = new XMLHttpRequest();
        xhr.lastResponseLen = 0;

        xhr.onprogress = this._onprogress;
        xhr.onreadystatechange = this._onreadystatechange;

        xhr.open('POST', this.controller, true);
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
            stratusAppInstance.processMessage(currentResponse, this);
        }
    }

    _onreadystatechange() {
        const xhr = this;

        if (xhr.readyState === XMLHttpRequest.DONE) {
            stratusAppInstance.httpRequests.splice(
                stratusAppInstance.httpRequests.indexOf(xhr), 1
            );

            if (xhr.status === 200) {
                stratusAppInstance.processMessage(xhr.responseText, xhr);
            }
        }
    }

    dispatch(eventName) {
        const xhr = this.getNewXMLHttpRequest();
        const componentData = {};

        for (let componentId in this.components) {
            let component = this.components[componentId];
            componentData[componentId] = component.getCriticalData();
        }

        Object.assign(componentData, componentData, this.buffer);

        const data = {
            token: this.token,
            componentData,
            eventName,
        };

        this.sendRequest(xhr, data);
        this.buffer = {};
    }

    sendRequest(xhr, data) {
        xhr.data = data;
        xhr.send('stratus_request=' + JSON.stringify(data));
        this.httpRequests.push(xhr);
    }

    processMessage(text, xhr) {
        if ('string' !== typeof(text)) {
            return;
        }

        let lines = text.split('%SSS%');
        for (let id in lines) {
            let line = lines[id];

            if (! line.length) {
                continue;
            }

            let message = JSON.parse(line);

            if ('boolean' === typeof(message.resend) &&
                true === message.resend &&
                'string' === typeof(message.code)
            ) {
                let newXhr = this.getNewXMLHttpRequest();
                let data = xhr.data;
                let result = eval(`(() => {${message.code}})()`);

                Object.assign(data, data, result);

                this.sendRequest(newXhr, data);
            } else {
                let HandlerClass = this.classes[message.handler.classId];
                let handler = HandlerClass[message.handler.method];

                handler.apply(null, Object.values(message.data));
            }
        }
    }
}
