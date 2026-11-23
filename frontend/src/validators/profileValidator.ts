/** The profile form's fields, as typed in the inputs. */
export interface ProfileFormValues {
  name: string;
  email: string;
}

/** The change-password form's fields; the confirmation is never sent to the backend. */
export interface PasswordFormValues {
  current_password: string;
  new_password: string;
  new_password_confirmation: string;
}

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

/** Same rules as the backend's UpdateProfileRequest; returns a message per invalid field. */
export function validateProfile(values: ProfileFormValues): Record<string, string> {
  const errors: Record<string, string> = {};
  const name = values.name.trim();
  const email = values.email.trim();

  if (!name) {
    errors.name = 'The name is required.';
  } else if (name.length > 255) {
    errors.name = 'The name may not be longer than 255 characters.';
  }

  if (!email) {
    errors.email = 'The email is required.';
  } else if (!EMAIL_PATTERN.test(email)) {
    errors.email = 'Enter a valid email address.';
  } else if (email.length > 255) {
    errors.email = 'The email may not be longer than 255 characters.';
  }

  return errors;
}

/** Same rules as the backend's ChangePasswordRequest, plus the confirmation check. */
export function validatePassword(values: PasswordFormValues): Record<string, string> {
  const errors: Record<string, string> = {};

  if (!values.current_password) {
    errors.current_password = 'The current password is required.';
  }

  if (!values.new_password) {
    errors.new_password = 'The new password is required.';
  } else if (values.new_password.length < 8) {
    errors.new_password = 'The new password must be at least 8 characters.';
  } else if (values.new_password.length > 255) {
    errors.new_password = 'The new password may not be longer than 255 characters.';
  } else if (values.new_password === values.current_password) {
    errors.new_password = 'The new password must be different from the current one.';
  }

  if (values.new_password_confirmation !== values.new_password) {
    errors.new_password_confirmation = 'The passwords do not match.';
  }

  return errors;
}
