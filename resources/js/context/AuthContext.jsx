import React, { createContext, useContext, useState, useEffect } from 'react';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [user, setUser] = useState(() => {
        const rootEl = document.getElementById('react-app');
        if (rootEl && rootEl.dataset.user) {
            try {
                return JSON.parse(rootEl.dataset.user);
            } catch (e) {}
        }
        const savedUser = localStorage.getItem('meditrack_user');
        return savedUser ? JSON.parse(savedUser) : null;
    });

    const [token, setToken] = useState(() => {
        return localStorage.getItem('meditrack_token') || null;
    });

    const [loading, setLoading] = useState(false);

    const login = async (email, password) => {
        setLoading(true);
        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email, password }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                return {
                    success: false,
                    message: data.message || 'Login failed. Please check your credentials.',
                    errors: data.data?.errors || null,
                };
            }

            const authToken = data.data.token;
            const authUser = data.data.user;

            localStorage.setItem('meditrack_token', authToken);
            localStorage.setItem('meditrack_user', JSON.stringify(authUser));

            setToken(authToken);
            setUser(authUser);

            return { success: true, user: authUser };
        } catch (error) {
            return {
                success: false,
                message: 'Network error communicating with the server. Please check your connection.',
            };
        } finally {
            setLoading(false);
        }
    };

    const logout = async () => {
        if (token) {
            try {
                await fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                    },
                });
            } catch (e) {
                console.warn('API logout error:', e);
            }
        }

        localStorage.removeItem('meditrack_token');
        localStorage.removeItem('meditrack_user');
        setToken(null);
        setUser(null);
    };

    const apiFetch = async (endpoint, options = {}) => {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            ...(options.headers || {}),
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(options.body);
        }

        const response = await fetch(endpoint, {
            ...options,
            headers,
        });

        const data = await response.json().catch(() => ({}));

        return {
            status: response.status,
            ok: response.ok,
            data,
        };
    };

    return (
        <AuthContext.Provider value={{ user, token, loading, login, logout, apiFetch }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    return useContext(AuthContext);
}
