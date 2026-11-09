const FIELD_LENGTH = 5; // Meter

type TentWidth  = 6 | 8 | 10 | 16 | 20;
type TentFields = 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10;
type TentSide   = 'left' | 'right';
type GableEnd   = 'front' | 'back';
type GableSupportPosition = 'corner-left' | 'left' | 'center' | 'right' | 'corner-right';

export class Leg {
    constructor(
        readonly boundaryIndex: number,
        readonly side: TentSide,
    ) {}
}

export class GableSupport {
    constructor(
        readonly end: GableEnd,
        readonly position: GableSupportPosition,
    ) {}
}

type AttachPoint = Leg | GableSupport;

export type DoorDirection = 'forward' | 'backward'; // relativ zum boundaryIndex (forward = Richtung höherer Index)

export class Door {
    readonly id = crypto.randomUUID();

    constructor(
        readonly attachesTo: Leg | GableSupport,
        readonly direction: DoorDirection,
        readonly width: number,
    ) {}

    /** Eindeutiger Schlüssel für den belegten Wandabschnitt */
    get sectionKey(): string {
        if (this.attachesTo instanceof Leg) {
            const fi = this.direction === 'forward'
                ? this.attachesTo.boundaryIndex
                : this.attachesTo.boundaryIndex - 1;
            return `side:${this.attachesTo.side}:${fi}`;
        }
        return `gable:${this.attachesTo.end}:${this.attachesTo.position}:${this.direction}`;
    }
}

// ─── Serialisierungstypen (vor Tent definiert) ───────────────────────────────

type LegRef          = { kind: 'leg';   boundaryIndex: number; side: TentSide };
type GableSupportRef = { kind: 'gable'; end: GableEnd; position: GableSupportPosition };

export interface TentJSON {
    width: TentWidth;
    fields: TentFields;
    doors: Array<{
        attachesTo: LegRef | GableSupportRef;
        direction: DoorDirection;
        width: number;
    }>;
}

// ─── Basis ───────────────────────────────────────────────────────────────────

export abstract class Tent {
    readonly id = crypto.randomUUID();
    abstract readonly width: TentWidth;

    get length(): number { return this.fields * FIELD_LENGTH; }

    readonly legs: Leg[];
    readonly gableSupports: GableSupport[];
    readonly doors: Door[] = [];

    constructor(
        readonly fields: TentFields,
        gablePositions: GableSupportPosition[],
    ) {
        this.legs = [];
        for (let i = 0; i <= fields; i++) {
            this.legs.push(new Leg(i, 'left'));
            this.legs.push(new Leg(i, 'right'));
        }

        const ends: GableEnd[] = ['front', 'back'];
        const allPositions: GableSupportPosition[] = ['corner-left', ...gablePositions, 'corner-right'];
        this.gableSupports = ends.flatMap(end =>
            allPositions.map(position => new GableSupport(end, position))
        );
    }

    attachDoor(ap: Leg | GableSupport, direction: DoorDirection, width: number): Door {
        const belongs = ([...this.legs, ...this.gableSupports] as (Leg | GableSupport)[]).includes(ap);
        if (!belongs) throw new Error('AttachPoint does not belong to this tent');

        if (ap instanceof Leg) {
            if (direction === 'backward' && ap.boundaryIndex === 0) throw new Error('No field before first boundary');
            if (direction === 'forward'  && ap.boundaryIndex === this.fields) throw new Error('No field after last boundary');
        }

        const door = new Door(ap, direction, width);
        if (this.doors.some(d => d.sectionKey === door.sectionKey)) throw new Error('Section already has a door');

        this.doors.push(door);
        return door;
    }

    detachDoor(door: Door): void {
        const index = this.doors.indexOf(door);
        if (index === -1) throw new Error('Door does not belong to this tent');
        this.doors.splice(index, 1);
    }

    toJSON(): TentJSON {
        return {
            width:  this.width,
            fields: this.fields,
            doors:  this.doors.map(d => ({
                attachesTo: d.attachesTo instanceof Leg
                    ? { kind: 'leg' as const, boundaryIndex: d.attachesTo.boundaryIndex, side: d.attachesTo.side }
                    : { kind: 'gable' as const, end: (d.attachesTo as GableSupport).end, position: (d.attachesTo as GableSupport).position },
                direction: d.direction,
                width: d.width,
            })),
        };
    }
}

// ─── Konkrete Zeltbreiten ─────────────────────────────────────────────────────

abstract class NarrowTent extends Tent {
    constructor(fields: TentFields) { super(fields, ['center']); }
}

abstract class WideTent extends Tent {
    constructor(fields: TentFields) { super(fields, ['left', 'center', 'right']); }
}

export class Tent6  extends NarrowTent { readonly width = 6  as const; }
export class Tent8  extends NarrowTent { readonly width = 8  as const; }
export class Tent10 extends NarrowTent { readonly width = 10 as const; }
export class Tent16 extends WideTent   { readonly width = 16 as const; }
export class Tent20 extends WideTent   { readonly width = 20 as const; }

// ─── fromJSON Factory ────────────────────────────────────────────────────────

export function fromJSON(json: TentJSON): Tent {
    const classes: Record<TentWidth, new (f: TentFields) => Tent> = {
        6: Tent6, 8: Tent8, 10: Tent10, 16: Tent16, 20: Tent20,
    };
    const tent = new classes[json.width](json.fields);

    for (const d of json.doors) {
        const ref = d.attachesTo;
        const ap = ref.kind === 'leg'
            ? tent.legs.find(l => l.boundaryIndex === ref.boundaryIndex && l.side === ref.side)!
            : tent.gableSupports.find(g => g.end === ref.end && g.position === ref.position)!;
        tent.attachDoor(ap, d.direction, d.width);
    }

    return tent;
}