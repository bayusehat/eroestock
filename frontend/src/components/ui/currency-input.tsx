"use client";

import * as React from "react";
import { Input } from "@/components/ui/input";
import {
  formatNumberForInput,
  formatNumberForInputLive,
  parseFormattedNumber,
} from "@/lib/format";
import { cn } from "@/lib/utils";

interface CurrencyInputProps
  extends Omit<React.ComponentProps<typeof Input>, "value" | "onChange" | "type"> {
  value?: number;
  onChange?: (value: number) => void;
  decimals?: number;
}

export const CurrencyInput = React.forwardRef<HTMLInputElement, CurrencyInputProps>(
  ({ value = 0, onChange, decimals = 2, className, ...props }, ref) => {
    const [displayValue, setDisplayValue] = React.useState("");
    const [isFocused, setIsFocused] = React.useState(false);

    React.useEffect(() => {
      if (!isFocused) {
        setDisplayValue(
          value !== 0 && value !== undefined && !Number.isNaN(value)
            ? formatNumberForInput(value, decimals)
            : ""
        );
      }
    }, [value, decimals, isFocused]);

    const handleFocus = (e: React.FocusEvent<HTMLInputElement>) => {
      setIsFocused(true);
      setDisplayValue(
        value !== 0 && value !== undefined && !Number.isNaN(value)
          ? formatNumberForInputLive(value, decimals)
          : ""
      );
      props.onFocus?.(e);
    };

    const handleBlur = (e: React.FocusEvent<HTMLInputElement>) => {
      setIsFocused(false);
      const num = parseFormattedNumber(displayValue);
      onChange?.(num);
      setDisplayValue(
        num !== 0 ? formatNumberForInput(num, decimals) : ""
      );
      props.onBlur?.(e);
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
      const raw = e.target.value;
      const num = parseFormattedNumber(raw);
      let formatted =
        num !== 0 && !Number.isNaN(num)
          ? formatNumberForInputLive(num, decimals)
          : "";
      if (formatted && /[,.]\s*$/.test(raw)) {
        formatted += ",";
      }
      setDisplayValue(formatted);
      onChange?.(num);
    };

    return (
      <Input
        ref={ref}
        type="text"
        inputMode="decimal"
        autoComplete="off"
        value={displayValue}
        onChange={handleChange}
        onFocus={handleFocus}
        onBlur={handleBlur}
        placeholder={props.placeholder ?? "0"}
        className={cn(className)}
        {...props}
      />
    );
  }
);

CurrencyInput.displayName = "CurrencyInput";
