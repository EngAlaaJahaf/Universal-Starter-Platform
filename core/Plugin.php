<?php

/**
 * Plugin hook system (SH-12b): minimal, pure, non-intrusive listeners keyed
 * by named hook points. No DI container, no config file — just register()
 * callables and fire them at flow points. When nothing is registered the
 * loop is a no-op, so wiring hooks can never alter existing behaviour.
 */
class Plugin
{
    private static $listeners = array();

    /** Register a callable at $point. Higher $priority runs first. */
    public static function register($point, callable $listener, $priority = 10)
    {
        self::$listeners[$point][$priority][] = $listener;
        ksort(self::$listeners[$point]);
    }

    /**
     * Fire $point, passing $context by reference so listeners can mutate it.
     * The FIRST non-null return value short-circuits and is returned.
     * Returns null when there are no listeners (safe no-op).
     */
    public static function hook($point, array &$context = array())
    {
        if (empty(self::$listeners[$point])) {
            return null;
        }
        foreach (self::$listeners[$point] as $group) {
            foreach ($group as $listener) {
                $result = $listener($context);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return null;
    }

    public static function hasHooks($point)
    {
        return !empty(self::$listeners[$point]);
    }

    /** Ordered (by priority) listener list for a point; empty when unbound. */
    public static function listeners($point)
    {
        return (array) (self::$listeners[$point] ?? array());
    }

    /** Test isolation helper — clears every registered listener. */
    public static function reset()
    {
        self::$listeners = array();
    }
}