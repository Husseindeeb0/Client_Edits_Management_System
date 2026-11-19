import { useState } from 'react';
import { useGetCompanyDetailsQuery, useGetPaginatedCompaniesQuery } from '../../client/companyApi';
import SearchSelect from '../SharedComponents/SearchSelect';

interface CompanySelectProps {
  id: string;
  value: number | null;
  onChange: (companyId: number) => void;
}

/** Choose a company: searchable (by company or client name), one page at a time. */
export default function CompanySelect({ id, value, onChange }: CompanySelectProps) {
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [pickedLabel, setPickedLabel] = useState<string>();

  const { data, isFetching } = useGetPaginatedCompaniesQuery({ search, page });
  // The current company may not be on this page, so get its name on its own (when editing)
  const { data: current } = useGetCompanyDetailsQuery(value ?? 0, { skip: value === null || pickedLabel !== undefined });

  return (
    <SearchSelect
      id={id}
      placeholder="Choose a company"
      selectedId={value}
      selectedLabel={pickedLabel ?? current?.name}
      options={(data?.data ?? []).map((company) => ({ id: company.id, label: company.name, hint: company.client_name }))}
      isLoading={isFetching}
      page={data?.meta.current_page ?? 1}
      lastPage={data?.meta.last_page ?? 1}
      onPageChange={setPage}
      onSearch={(term) => {
        setSearch(term);
        setPage(1);
      }}
      onSelect={(option) => {
        setPickedLabel(option.label);
        onChange(option.id);
      }}
    />
  );
}
