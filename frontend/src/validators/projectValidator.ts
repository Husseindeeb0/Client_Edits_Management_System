/** The project form's fields, as typed in the inputs. */
export interface ProjectFormValues {
  name: string;
  /** The selected company's id, '' when none is chosen. */
  company_id: string;
}

/** Same rules as the backend's StoreProjectRequest; returns a message per invalid field. */
export function validateProject(values: ProjectFormValues): Record<string, string> {
  const errors: Record<string, string> = {};
  const name = values.name.trim();

  if (!name) {
    errors.name = 'The name is required.';
  } else if (name.length > 255) {
    errors.name = 'The name may not be longer than 255 characters.';
  }

  if (!values.company_id) {
    errors.company_id = 'Choose a company.';
  }

  return errors;
}
