export abstract class Failure {
    public constructor(public readonly message: string) {}
}

export class ServerFailure extends Failure {
    public constructor(message: string = 'Terjadi kesalahan pada server.') {
        super(message);
    }
}

export class ValidationFailure extends Failure {
    public constructor(
        message: string = 'Data tidak valid.',
        public readonly errors: Record<string, string[]> = {}
    ) {
        super(message);
    }
}

export class ConnectionFailure extends Failure {
    public constructor(message: string = 'Tidak ada koneksi internet.') {
        super(message);
    }
}
