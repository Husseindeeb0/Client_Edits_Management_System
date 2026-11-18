/** The company form's fields, as typed in the inputs. */
export interface CompanyFormValues {
  name: string;
  /** The selected client's id, '' when none is chosen. */
  client_id: string;
}

/** Same rules as the backend's StoreCompanyRequest; returns a message per invalid field. */
export function validateCompany(values: CompanyFormValues): Record<string, string> {
  const errors: Record<string, string> = {};
  const name = values.name.trim();

  if (!name) {
    errors.name = 'The name is required.';
  } else if (name.length > 255) {
    errors.name = 'The name may not be longer than 255 characters.';
  }

  if (!values.client_id) {
    errors.client_id = 'Choose a client.';
  }

  return errors;
}
