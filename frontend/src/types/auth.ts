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

/** UpdateProfileRequest: send only the fields that changed. */
export type UpdateProfilePayload = Partial<Pick<AuthUser, 'name' | 'email'>>;

/** ChangePasswordRequest (the confirmation input stays in the frontend) */
export interface ChangePasswordPayload {
  current_password: string;
  new_password: string;
}
