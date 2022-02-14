/**
 * WebSocket
 *
 * @param url string
 * @param reconnect integer
 * @constructor
 */
function Ws(url, reconnect)
{
    this._url = url;
    this.reconnect = reconnect;
    this.connect();
}

/**
 * Состояния подключения
 * @type {{CONNECTING: number, OPEN: number, CLOSING: number, CLOSED: number}}
 */
Ws.prototype.State = {
    CONNECTING: 0,
    OPEN: 1,
    CLOSING: 2,
    CLOSED: 3
};

Ws.prototype.connect = function() {
    this.connected = false;
    this._ws = new WebSocket(this._url);
    this._ws.onopen = (this._onopen).bind(this);
    this._ws.onclose = (this._onclose).bind(this);
    this._ws.onmessage = (this._onmessage).bind(this);
    this._ws.onerror = (this._onerror).bind(this);
};

/**
 * Вызывает при открытии соединения
 * @private
 */
Ws.prototype._onopen = function() {
    this.connected = true;
    if (this.hasOwnProperty("onopen")) {
        if (typeof this.onopen == "function") {
            this.onopen();
        }
    } else {
        console_log('Connected to ' + this._url);
    }
};

/**
 * Вызывается при закрытии соединения
 * @private
 */
Ws.prototype._onclose = function() {
    this.connected = false;
    if (this.hasOwnProperty("onclose")) {
        if (typeof this.onclose == "function") {
            this.onclose();
        }
    } else {
        if (this.reconnect > 0) {
            console_log('Connection lost to ' + this._url);
            console_log('Trying restore connection to ' + this._url + 'in ' + this.reconnect + ' seconds');
            setTimeout(function() {

                this.connect();

            }.bind(this), this.reconnect*1000);
        } else {
            console_log('Disconnected from ' + this._url);
        }
    }
};

/**
 * Вызывается при ошибке
 * @param error mixed
 * @private
 */
Ws.prototype._onerror = function(error) {
    console_log(error);
    if (this.hasOwnProperty("onerror")) {
        if (typeof this.onerror == "function") {
            this.onerror(error);
        }
    }
};

/**
 * Вызывается при получении сообщения от вебсокета
 * @param message mixed
 * @private
 */
Ws.prototype._onmessage = function(message) {
    if (this.hasOwnProperty("onmessage")) {
        if (typeof this.onmessage == "function") {
            this.onmessage(message);
        }
    } else {
        console_log("Received message: " + message);
        console_log(message);
    }
};

/**
 * Закрытие соединения
 */
Ws.prototype.close = function() {
    this.reconnect = 0;
    this.connected = false;
    this._ws.close();
};

/**
 * Получения состояния соедениения
 * @returns {number}
 */
Ws.prototype.getState = function() {
    return this._ws.readyState;
};

/**
 * Отправка сообщения на вебсокет
 * @param message string
 */
Ws.prototype.send = function(message) {
    if (this.getState() == this.State.CONNECTING) {
        setTimeout(function () {
            this.send(message);
        }.bind(this), 1000);
    } else {
        if (this.connected) {
            console_log('Sent: ' + message);
            this._ws.send(message);
        }/* else if (this.reconnect > 0) {
            console_log('Connection lost to ' + this._url);
            console_log('Trying restore connection to ' + this._url + 'in ' + this.reconnect + ' seconds');
            setTimeout(function() {

                this.connect();

            }.bind(this), this.reconnect*1000);
        }*/ else {
            console_log("Ws connection error");
        }
    }
};


/**
 * Если на странице есть елемент с ИД = wss-data
 * то создаем вебсокет подключение
 *
 * ws://echo.websocket.org  - этот сокс можно использовать для тестирования вопрос-ответ
 * он присылает в ответ то что на него отправляется - эхо сервис
 */
