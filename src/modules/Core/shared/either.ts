export type Either<L, R> = Left<L, R> | Right<L, R>;

export class Left<L, R> {
    public constructor(public readonly value: L) {}

    public isLeft(): this is Left<L, R> {
        return true;
    }

    public isRight(): this is Right<L, R> {
        return false;
    }

    public fold<T>(onLeft: (l: L) => T, onRight: (r: R) => T): T {
        return onLeft(this.value);
    }
}

export class Right<L, R> {
    public constructor(public readonly value: R) {}

    public isLeft(): this is Left<L, R> {
        return false;
    }

    public isRight(): this is Right<L, R> {
        return true;
    }

    public fold<T>(onLeft: (l: L) => T, onRight: (r: R) => T): T {
        return onRight(this.value);
    }
}

export const left = <L, R>(l: L): Either<L, R> => new Left(l);
export const right = <L, R>(r: R): Either<L, R> => new Right(r);
