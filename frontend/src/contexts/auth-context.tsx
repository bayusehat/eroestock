"use client";

import React, { createContext, useCallback, useContext, useEffect, useState } from "react";
import { apiClient, setAuthToken, clearAuthToken } from "@/lib/api";
import type { User } from "@/types";

interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  hasPermission: (permission: string) => boolean;
  hasRole: (role: string) => boolean;
}

const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  const checkAuth = useCallback(async () => {
    try {
      const res = await apiClient.get<{ data: User } | User>("/auth/me");
      const body = res.data;
      setUser("data" in body && body.data ? body.data : (body as User));
    } catch {
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    checkAuth();
  }, [checkAuth]);

  const login = useCallback(
    async (email: string, password: string) => {
      const res = await apiClient.post<{ data?: { token: string; user: User }; token?: string; user?: User }>(
        "/auth/login",
        { email, password }
      );
      const body = res.data as { data?: { token: string; user: User }; token?: string; user?: User };
      const token = body.data?.token ?? body.token;
      const userData = body.data?.user ?? body.user;
      if (token) setAuthToken(token);
      if (userData) setUser(userData);
    },
    []
  );

  const logout = useCallback(async () => {
    try {
      await apiClient.post("/auth/logout");
    } finally {
      clearAuthToken();
      setUser(null);
    }
  }, []);

  const hasPermission = useCallback(
    (permission: string) => {
      if (!user) return false;
      const perms = user.permissions ?? [];
      return perms.some((p) => (typeof p === "string" ? p : p?.name) === permission);
    },
    [user]
  );

  const hasRole = useCallback(
    (role: string) => {
      if (!user) return false;
      return user.roles?.some((r) => r.name === role) ?? false;
    },
    [user]
  );

  return (
    <AuthContext.Provider
      value={{ user, loading, login, logout, hasPermission, hasRole }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error("useAuth must be used within AuthProvider");
  }
  return context;
}
