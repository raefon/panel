export enum SocketEvent {
    DAEMON_MESSAGE = 'daemon message',
    DAEMON_ERROR = 'daemon error',
    INSTALL_OUTPUT = 'install output',
    INSTALL_STARTED = 'install started',
    INSTALL_COMPLETED = 'install completed',
    CONSOLE_OUTPUT = 'console output',
    STATUS = 'status',
    STATS = 'stats',
    JSON = 'json',
    TRANSFER_LOGS = 'transfer logs',
    TRANSFER_STATUS = 'transfer status',
    SNAPSHOT_COMPLETED = 'snapshot completed',
    SNAPSHOT_RESTORE_COMPLETED = 'snapshot restore completed',
}

export enum SocketRequest {
    SEND_LOGS = 'send logs',
    SEND_STATS = 'send stats',
    SEND_JSON = 'send json',
    SET_STATE = 'set state',
}
