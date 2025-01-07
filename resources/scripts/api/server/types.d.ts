export type ServerStatus = 'installing' | 'install_failed' | 'suspended' | 'restoring_snapshot' | null;

export interface ServerSnapshot {
    uuid: string;
    isSuccessful: boolean;
    isLocked: boolean;
    name: string;
    snapcontent: string;
    bytes: number;
    createdAt: Date;
    completedAt: Date | null;
}

export interface ServerRocketVariable {
    name: string;
    description: string;
    envVariable: string;
    defaultValue: string;
    serverValue: string;
    isEditable: boolean;
    rules: string[];
}
