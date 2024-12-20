declare function route(name: string, params?: Record<string, any>, absolute?: boolean): string;

interface RouteFunction {
    (name: string, params?: Record<string, any>, absolute?: boolean): string;
    current: (name: string) => boolean;
}

declare global {
    function route(name: string, params?: Record<string, any>, absolute?: boolean): string;
    const route: RouteFunction;
}

export {};