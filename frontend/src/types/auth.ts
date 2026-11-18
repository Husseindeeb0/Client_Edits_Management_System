/** AuthenticatedUserResource */
export interface AuthUser {
  id: number;
  name: string;
  email: string;
}

/** LoginRequest */
export interface LoginPayload {
  email: string;
  password: string;
}
