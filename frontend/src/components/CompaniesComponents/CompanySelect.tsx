import { useState } from 'react';
import { useGetCompanyDetailsQuery, useGetInfiniteCompaniesInfiniteQuery } from '../../client/companyApi';
import SearchSelect from '../SharedComponents/SearchSelect';

interface CompanySelectProps {
  id: string;
  value: number | null;
  onChange: (companyId: number) => void;
}

/** Choose a company: searchable (by company or client name), more companies load while scrolling. */
export default function CompanySelect({ id, value, onChange }: CompanySelectProps) {
  const [search, setSearch] = useState('');
  const [pickedLabel, setPickedLabel] = useState<string>();

  const { data, isFetching, isFetchingNextPage, hasNextPage, fetchNextPage } = useGetInfiniteCompaniesInfiniteQuery(search);
  // The current company may not be loaded yet, so get its name on its own (when editing)
  const { data: current } = useGetCompanyDetailsQuery(value ?? 0, { skip: value === null || pickedLabel !== undefined });

  return (
    <SearchSelect
      id={id}
      placeholder="Choose a company"
      selectedId={value}
      selectedLabel={pickedLabel ?? current?.name}
      options={(data?.pages ?? []).flatMap((page) =>
        page.data.map((company) => ({ id: company.id, label: company.name, hint: company.client_name })),
      )}
      isLoading={isFetching && !isFetchingNextPage}
      isLoadingMore={isFetchingNextPage}
      hasMore={hasNextPage}
      onLoadMore={fetchNextPage}
      onSearch={setSearch}
      onSelect={(option) => {
        setPickedLabel(option.label);
        onChange(option.id);
      }}
    />
  );
}
