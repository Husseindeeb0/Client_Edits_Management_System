/** The client form's fields, as typed in the inputs. */
export interface ClientFormValues {
  name: string;
  phone_number: string;
  email: string;
  last_follow_up_date: string;
}

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

/** Same rules as the backend's StoreClientRequest; returns a message per invalid field. */
export function validateClient(values: ClientFormValues): Record<string, string> {
  const errors: Record<string, string> = {};
  const name = values.name.trim();
  const phone = values.phone_number.trim();
  const email = values.email.trim();

  if (!name) {
    errors.name = 'The name is required.';
  } else if (name.length > 255) {
    errors.name = 'The name may not be longer than 255 characters.';
  }

  if (!phone) {
    errors.phone_number = 'The phone number is required.';
  } else if (phone.length > 30) {
    errors.phone_number = 'The phone number may not be longer than 30 characters.';
  }

  if (email && !EMAIL_PATTERN.test(email)) {
    errors.email = 'Enter a valid email address.';
  } else if (email.length > 255) {
    errors.email = 'The email may not be longer than 255 characters.';
  }

  return errors;
}
