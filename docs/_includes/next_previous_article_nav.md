---
{% if include.previous_article_name %}
    {% if include.previous_article_url %}
        {% assign previous_article_url = include.previous_article_url %}
    {% else %}
        {% capture previous_article_url %}{{ include.previous_article_name | downcase }}{% endcapture %}
    {% endif %}
{% endif %}

{% if include.next_article_name %}
    {% if include.next_article_url %}
        {% assign next_article_url = include.next_article_url %}
    {% else %}
        {% capture next_article_url %}{{ include.next_article_name | downcase }}{% endcapture %}
    {% endif %}
{% endif %}

{% if previous_article_url %}
  <a href="{{ previous_article_url }}" class="is-pulled-left is-size-6">
    <span class="icon">
      <i class="fas fa-angle-left"></i>
    </span>
    {{ include.previous_article_name }}
  </a>
{% endif %}

{% if next_article_url %}
  <a href="{{ next_article_url }}" class="is-pulled-right is-size-6">
    {{ include.next_article_name }}
    <span class="icon">
      <i class="fas fa-angle-right"></i>
    </span>
  </a>
{% endif %}